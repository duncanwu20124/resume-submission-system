<?php

namespace App\Services;

use App\Models\AllocationResultModel;
use App\Models\AllocationRunModel;
use App\Models\StudentPreferenceModel;
use App\Models\StudentScoreModel;
use App\Models\UniversityCapacityModel;
use RuntimeException;

class AllocationService
{
    /**
     * Score first, deterministic lottery for equal scores, then the student's
     * highest-ranked school that still has capacity.
     *
     * @param array<int,array<string,mixed>> $candidates
     * @param array<string,int> $capacities
     * @return array<int,array<string,mixed>>
     */
    public function allocate(array $candidates, array $capacities, string $seed): array
    {
        foreach ($candidates as &$candidate) {
            $candidate['lottery_order'] = hash_hmac('sha256', (string) $candidate['student_db_id'], $seed);
        }
        unset($candidate);

        usort($candidates, static function (array $a, array $b): int {
            $scoreComparison = (float) $b['total_score'] <=> (float) $a['total_score'];
            return $scoreComparison !== 0
                ? $scoreComparison
                : strcmp($a['lottery_order'], $b['lottery_order']);
        });

        $results = [];
        foreach ($candidates as $index => $candidate) {
            $school = null;
            $preferenceRank = null;
            foreach ($candidate['choices'] as $choiceIndex => $choice) {
                if (($capacities[$choice] ?? 0) > 0) {
                    $school = $choice;
                    $preferenceRank = $choiceIndex + 1;
                    $capacities[$choice]--;
                    break;
                }
            }

            $results[] = [
                'student_db_id' => (int) $candidate['student_db_id'],
                'score_snapshot' => number_format((float) $candidate['total_score'], 2, '.', ''),
                'lottery_order' => $candidate['lottery_order'],
                'overall_rank' => $index + 1,
                'university_name_snapshot' => $school,
                'preference_rank' => $preferenceRank,
                'result_status' => $school === null ? 'unassigned' : 'admitted',
                'reason' => $school === null ? '六個志願在輪到分發時皆已額滿。' : null,
            ];
        }

        return $results;
    }

    public function createPreview(int $adminId): int
    {
        $db = db_connect();
        $preferences = (new StudentPreferenceModel())->listWithStudents();
        if ($preferences === []) {
            throw new RuntimeException('目前沒有已送出志願序的學生。');
        }

        $scoreModel = new StudentScoreModel();
        $candidates = [];
        $missing = [];
        foreach ($preferences as $preference) {
            $score = $scoreModel->findByStudent((int) $preference['student_db_id']);
            if (!$score || $score['status'] !== StudentScoreModel::STATUS_CONFIRMED) {
                $missing[] = $preference['student_name'] . '（' . $preference['student_number'] . '）';
                continue;
            }
            $choices = [];
            for ($rank = 1; $rank <= StudentPreferenceModel::CHOICE_COUNT; $rank++) {
                $choices[] = $preference['choice_' . $rank];
            }
            $candidates[] = [
                'student_db_id' => (int) $preference['student_db_id'],
                'total_score' => (float) $score['total_score'],
                'choices' => $choices,
            ];
        }
        if ($missing !== []) {
            throw new RuntimeException('以下學生尚未確認評分：' . implode('、', $missing));
        }

        $capacities = [];
        foreach ((new UniversityCapacityModel())->where('is_active', 1)->findAll() as $university) {
            $capacities[$university['name']] = (int) $university['capacity'];
        }
        $seed = bin2hex(random_bytes(32));
        $results = $this->allocate($candidates, $capacities, $seed);
        $now = date('Y-m-d H:i:s');

        $db->transStart();
        $runModel = new AllocationRunModel();
        $runId = (int) $runModel->insert([
            'status' => AllocationRunModel::STATUS_PREVIEW,
            'random_seed' => $seed,
            'started_by' => $adminId,
            'started_at' => $now,
        ], true);
        $resultModel = new AllocationResultModel();
        foreach ($results as $result) {
            $result['allocation_run_id'] = $runId;
            $result['created_at'] = $now;
            $resultModel->insert($result);
        }
        $db->transComplete();
        if (!$db->transStatus()) {
            throw new RuntimeException('分發預覽建立失敗，資料庫已回復。');
        }

        return $runId;
    }

    public function publish(int $runId, string $note): bool
    {
        $db = db_connect();
        $runModel = new AllocationRunModel();
        $run = $runModel->find($runId);
        if (!$run || $run['status'] !== AllocationRunModel::STATUS_PREVIEW) {
            return false;
        }

        $db->transStart();
        $db->table('allocation_runs')->where('status', AllocationRunModel::STATUS_PUBLISHED)
            ->update(['status' => AllocationRunModel::STATUS_SUPERSEDED, 'updated_at' => date('Y-m-d H:i:s')]);
        $runModel->update($runId, [
            'status' => AllocationRunModel::STATUS_PUBLISHED,
            'published_at' => date('Y-m-d H:i:s'),
            'revision_note' => $note,
        ]);
        $db->transComplete();
        return $db->transStatus();
    }
}
