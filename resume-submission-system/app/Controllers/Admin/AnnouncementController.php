<?php

namespace App\Controllers\Admin;

use App\Models\AnnouncementModel;
use App\Models\AuditLogModel;

class AnnouncementController extends BaseAdminController
{
    public function announcements()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權存取公告管理功能。');
        }

        $model = new AnnouncementModel();
        $announcements = $model->orderBy('created_at', 'DESC')->findAll();

        return $this->renderAdminView('admin/announcements', ['announcements' => $announcements]);
    }

    public function createAnnouncement()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權存取公告管理功能。');
        }

        $title = trim((string) $this->request->getVar('title'));
        $content = trim((string) $this->request->getVar('content'));
        $displayType = $this->request->getVar('display_type');

        if (!in_array($displayType, ['list', 'marquee'], true)) {
            $displayType = 'list';
        }

        if (empty($title) || empty($content)) {
            return redirect()->to('/AdminController/announcements')->with('error', '標題與內容皆為必填項目。');
        }

        $model = new AnnouncementModel();
        $model->save([
            'title' => $title,
            'content' => $content,
            'display_type' => $displayType,
            'is_active' => 1,
            'admin_id' => session()->get('admin_id'),
        ]);

        AuditLogModel::log(
            '公告管理',
            "發布系統公告「{$title}」",
            '成功',
            "顯示形式：{$displayType}"
        );

        return redirect()->to('/AdminController/announcements')->with('success', '公告已成功發布。');
    }

    public function toggleAnnouncement($id)
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權存取公告管理功能。');
        }

        $model = new AnnouncementModel();
        $announcement = $model->find($id);

        if ($announcement) {
            $newActive = $announcement['is_active'] ? 0 : 1;
            $statusText = $newActive ? '啟用' : '停用';
            $model->update($id, ['is_active' => $newActive]);

            AuditLogModel::log(
                '公告管理',
                "變更公告「{$announcement['title']}」狀態為 [{$statusText}]",
                '成功'
            );
        }

        return redirect()->to('/AdminController/announcements')->with('success', '公告狀態已更新。');
    }

    public function deleteAnnouncement($id)
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權存取公告管理功能。');
        }

        $model = new AnnouncementModel();
        $announcement = $model->find($id);
        $title = $announcement ? $announcement['title'] : "#{$id}";
        $model->delete($id);

        AuditLogModel::log(
            '公告管理',
            "刪除系統公告「{$title}」",
            '成功'
        );

        return redirect()->to('/AdminController/announcements')->with('success', '公告已刪除。');
    }
}
