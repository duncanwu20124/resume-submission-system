<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentPreferenceModel extends Model
{
    public const CHOICE_COUNT = 6;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SUBMITTED = 'submitted';

    protected $DBGroup          = 'default';
    protected $table            = 'student_preferences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'student_db_id',
        'choice_1', 'choice_2', 'choice_3', 'choice_4', 'choice_5', 'choice_6',
        'status', 'submitted_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByStudent(int $studentDbId): ?array
    {
        return $this->where('student_db_id', $studentDbId)->first();
    }

    public function isLocked(array $preference): bool
    {
        return $preference['status'] === self::STATUS_SUBMITTED;
    }

    /**
     * @return string[] all 6 slots in rank order; unfilled slots are ''
     */
    public function choicesOf(array $preference): array
    {
        $choices = [];
        for ($i = 1; $i <= self::CHOICE_COUNT; $i++) {
            $choices[] = $preference['choice_' . $i];
        }

        return $choices;
    }

    /**
     * @return string[] only the filled-in choices, in rank order
     */
    public function filledChoicesOf(array $preference): array
    {
        return array_values(array_filter(
            $this->choicesOf($preference),
            static fn (string $choice): bool => $choice !== ''
        ));
    }

    /**
     * Saves the student's current picks as an editable draft (0-6 choices
     * allowed). Returns false if the student's preference is already locked.
     */
    public function saveDraft(int $studentDbId, array $choices): bool
    {
        $existing = $this->findByStudent($studentDbId);

        if ($existing && $this->isLocked($existing)) {
            return false;
        }

        $data = $this->choicesToColumns($choices);
        $data['status'] = self::STATUS_DRAFT;

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        $data['student_db_id'] = $studentDbId;

        return (bool) $this->insert($data);
    }

    /**
     * Locks in a student's final preference order. Returns false if this
     * student's preference is already locked.
     */
    public function submit(int $studentDbId, array $choices): bool
    {
        $existing = $this->findByStudent($studentDbId);

        if ($existing && $this->isLocked($existing)) {
            return false;
        }

        $data = $this->choicesToColumns($choices);
        $data['status']       = self::STATUS_SUBMITTED;
        $data['submitted_at'] = date('Y-m-d H:i:s');

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        $data['student_db_id'] = $studentDbId;

        return (bool) $this->insert($data);
    }

    /**
     * @return array<string, string>
     */
    private function choicesToColumns(array $choices): array
    {
        $padded = array_pad(array_values($choices), self::CHOICE_COUNT, '');
        $data   = [];

        foreach ($padded as $index => $choice) {
            $data['choice_' . ($index + 1)] = $choice;
        }

        return $data;
    }

    /**
     * Clears a student's saved preference (draft or submitted) so they can
     * start over. Returns false if the student had nothing saved.
     */
    public function resetByStudent(int $studentDbId): bool
    {
        $preference = $this->findByStudent($studentDbId);

        if (!$preference) {
            return false;
        }

        return $this->delete($preference['id']);
    }

    /**
     * All final (submitted) preferences joined with the owning student's
     * basic info, newest submission first. Drafts are excluded.
     */
    public function listWithStudents(): array
    {
        return $this->select('student_preferences.*, students.student_id as student_number, students.name as student_name, students.email as student_email')
            ->join('students', 'students.id = student_preferences.student_db_id')
            ->where('student_preferences.status', self::STATUS_SUBMITTED)
            ->orderBy('student_preferences.submitted_at', 'DESC')
            ->findAll();
    }
}
