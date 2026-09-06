<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;

class FeedbackController extends BaseController
{
    public function index()
    {
        return view('student/feedback', [
            'student' => [
                'id'         => session()->get('student_db_id'),
                'student_id' => session()->get('student_id'),
                'name'       => session()->get('student_name'),
                'email'      => session()->get('student_email'),
            ],
        ]);
    }
}