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

    public function announcement(int $id): string
    {
        $announcement = (new AnnouncementModel())
            ->where('id', $id)
            ->where('is_active', 1)
            ->first();

        if ($announcement === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('找不到指定公告。');
        }

        return view('announcement_detail', ['announcement' => $announcement]);
    }
}
