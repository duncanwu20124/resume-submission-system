<?php

namespace App\Controllers\Student;

use App\Config\PreferenceSettings;
use App\Config\Universities;
use App\Controllers\BaseController;
use App\Models\StudentModel;
use App\Models\StudentPreferenceModel;

class PreferenceController extends BaseController
{
    public function index()
    {
        $studentDbId  = (int) session()->get('student_db_id');
        $studentModel = new StudentModel();
        $student      = $studentModel->find($studentDbId);

        $prefModel  = new StudentPreferenceModel();
        $preference = $prefModel->findByStudent($studentDbId);
        $isLocked   = $preference && $prefModel->isLocked($preference);

        return view('student/preferences', [
            'student'       => $student,
            'preference'    => $preference,
            'isLocked'      => $isLocked,
            'choices'       => $preference ? $prefModel->choicesOf($preference) : [],
            'filledChoices' => $preference ? $prefModel->filledChoicesOf($preference) : [],
            'universities'  => Universities::$list,
            'deadline'      => PreferenceSettings::DEADLINE,
            'pastDeadline'  => strtotime(PreferenceSettings::DEADLINE) < time(),
        ]);
    }

    public function save()
    {
        $studentDbId = (int) session()->get('student_db_id');
        $prefModel   = new StudentPreferenceModel();
        $existing    = $prefModel->findByStudent($studentDbId);

        if ($existing && $prefModel->isLocked($existing)) {
            return redirect()->to('/student/preferences')->with('error', '您的志願序已經送出，無法再次修改。');
        }

        if (strtotime(PreferenceSettings::DEADLINE) < time()) {
            return redirect()->to('/student/preferences')->with('error', '已超過志願選填截止時間，無法儲存或送出。');
        }

        $isSubmit = $this->request->getPost('action') === 'submit';

        $choices = $this->request->getPost('choices');
        $choices = is_array($choices) ? array_values(array_filter(array_map('trim', $choices), static fn ($v) => $v !== '')) : [];

        if (count($choices) > StudentPreferenceModel::CHOICE_COUNT) {
            return redirect()->to('/student/preferences')->with('error', '志願數量超過上限，最多只能選擇 6 個。');
        }

        if (count(array_unique($choices)) !== count($choices)) {
            return redirect()->to('/student/preferences')->with('error', '志願清單中有重複的學校，請確認後再送出。');
        }

        foreach ($choices as $choice) {
            if (!Universities::isValid($choice)) {
                return redirect()->to('/student/preferences')->with('error', '志願清單包含無效的學校，請重新選擇。');
            }
        }

        if ($isSubmit) {
            if (count($choices) !== StudentPreferenceModel::CHOICE_COUNT) {
                return redirect()->to('/student/preferences')->with('error', '請選擇並排序恰好 6 個志願後再送出。');
            }

            if (!$prefModel->submit($studentDbId, $choices)) {
                return redirect()->to('/student/preferences')->with('error', '志願序已送出過，無法重複送出。');
            }

            return redirect()->to('/student/preferences')->with('success', '志願序已成功送出！送出後將無法再修改，請確認內容無誤。');
        }

        $prefModel->saveDraft($studentDbId, $choices);

        return redirect()->to('/student/preferences')->with('success', '草稿已儲存，您可以隨時回來繼續編輯，直到截止時間或按下最終送出為止。');
    }

    public function receipt()
    {
        $studentDbId = (int) session()->get('student_db_id');
        $student     = (new StudentModel())->find($studentDbId);

        $prefModel  = new StudentPreferenceModel();
        $preference = $prefModel->findByStudent($studentDbId);

        if (!$preference || !$prefModel->isLocked($preference)) {
            return redirect()->to('/student/preferences')->with('error', '尚未送出志願序，無法產生確認單。');
        }

        return view('student/preference_receipt', [
            'student'    => $student,
            'preference' => $preference,
            'choices'    => $prefModel->choicesOf($preference),
        ]);
    }
}
