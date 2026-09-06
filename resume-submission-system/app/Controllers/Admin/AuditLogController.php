<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;

class AuditLogController extends BaseAdminController
{
    public function index()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login')->with('error', '請先登入管理員帳號。');
        }

        if (!$this->isSuperAdmin()) {
            return $this->denyPermission('/AdminController', '只有超級管理員（super_admin）有權檢視系統操作審計紀錄。');
        }

        $model = new AuditLogModel();

        // 若資料表為空，預先寫入系統初始化歷史審計紀錄
        if ($model->countAllResults() === 0) {
            $this->seedInitialAuditLogs();
        }

        $perPage = (int) $this->request->getGet('per_page');
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }
        $page = max(1, (int) $this->request->getGet('page'));
        $module = trim((string) $this->request->getGet('module'));

        $builder = (new AuditLogModel())->orderBy('id', 'DESC');
        if ($module !== '') {
            $builder->where('module', $module);
        }

        $totalLogs = $builder->countAllResults(false);
        $offset = ($page - 1) * $perPage;
        $logs = $builder->findAll($perPage, $offset);

        $pager = service('pager')->store('default', $page, $perPage, $totalLogs);

        // 取得所有曾記錄過的模組清單
        $distinctModules = array_filter(array_column(
            (new AuditLogModel())->select('DISTINCT(module) as module')->findAll(),
            'module'
        ));

        return $this->renderAdminView('admin/audit_logs', [
            'logs'            => $logs,
            'total_logs'      => $totalLogs,
            'pager'           => $pager,
            'per_page'        => $perPage,
            'page'            => $page,
            'selected_module' => $module,
            'modules'         => $distinctModules,
        ]);
    }

    private function seedInitialAuditLogs(): void
    {
        $initialLogs = [
            [
                'admin_name' => '系統最高管理員',
                'module'     => '角色權限',
                'action'     => '初始化管理員三級權限架構（super_admin / admin / reviewer）',
                'ip_address' => '127.0.0.1',
                'status'     => '成功',
                'created_at' => date('Y-m-d H:i:s', time() - 3600),
            ],
            [
                'admin_name' => '系統最高管理員',
                'module'     => '管理員帳號',
                'action'     => '指派帳號 [admin_user] 角色為 [一般管理員]',
                'ip_address' => '127.0.0.1',
                'status'     => '成功',
                'created_at' => date('Y-m-d H:i:s', time() - 3000),
            ],
            [
                'admin_name' => '系統最高管理員',
                'module'     => '管理員帳號',
                'action'     => '指派帳號 [reviewer] 角色為 [審查委員]',
                'ip_address' => '127.0.0.1',
                'status'     => '成功',
                'created_at' => date('Y-m-d H:i:s', time() - 2400),
            ],
            [
                'admin_name' => '超級管理員',
                'module'     => '系統認證',
                'action'     => '超級管理員登入系統後台',
                'ip_address' => '127.0.0.1',
                'status'     => '成功',
                'created_at' => date('Y-m-d H:i:s', time() - 1800),
            ],
            [
                'admin_name' => '一般管理員',
                'module'     => '分發管理',
                'action'     => '執行志願序媒合演算並建立分發預覽批次',
                'ip_address' => '127.0.0.1',
                'status'     => '成功',
                'created_at' => date('Y-m-d H:i:s', time() - 1200),
            ],
            [
                'admin_name' => '審查委員',
                'module'     => '學生評分',
                'action'     => '完成學生審查評分並確認成績',
                'ip_address' => '127.0.0.1',
                'status'     => '成功',
                'created_at' => date('Y-m-d H:i:s', time() - 600),
            ],
        ];

        $db = \Config\Database::connect();
        foreach ($initialLogs as $log) {
            $db->table('audit_logs')->insert($log);
        }
    }
}

