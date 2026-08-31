<?php

namespace App\Controllers;

use App\Models\AnnouncementModel;

class Home extends BaseController
{
    public function index(): string
    {
        $announcementModel = new AnnouncementModel();

        return view('home', ['announcements' => $announcementModel->getActive()]);
    }
}
