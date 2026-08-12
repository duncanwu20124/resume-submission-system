<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $studentData = [
            'student_id'    => session()->get('student_id'),
            'student_name'  => session()->get('student_name'),
            'student_email' => session()->get('student_email'),
        ];

        // Placeholder for uploaded files list (ready for S-04 / S-05 / S-06 integration)
        $files = [];

        return view('student/dashboard', [
            'student' => $studentData,
            'files'   => $files,
        ]);
    }
}
