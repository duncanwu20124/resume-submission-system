<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\AllocationRunModel;
use App\Models\StudentModel;
use App\Models\StudentPreferenceModel;

class ResultController extends BaseController
{
    public function index()
    {
        $studentId = (int) session()->get('student_db_id');
        $run = (new AllocationRunModel())->latestPublished();
        $result = $run ? db_connect()->table('allocation_results')
            ->where('allocation_run_id', $run['id'])->where('student_db_id', $studentId)
            ->get()->getRowArray() : null;
        $preferenceModel = new StudentPreferenceModel();
        $preference = $preferenceModel->findByStudent($studentId);

        return view('student/result', [
            'student' => (new StudentModel())->find($studentId), 'run' => $run, 'result' => $result,
            'choices' => $preference ? $preferenceModel->choicesOf($preference) : [],
        ]);
    }
}
