<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'admin_id',
        'admin_name',
        'module',
        'action',
        'ip_address',
        'user_agent',
        'status',
        'details',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * 便捷記錄管理員操作日誌
     */
    public static function log(
        string $module,
        string $action,
        string $status = '成功',
        ?string $details = null,
        ?int $adminId = null,
        ?string $adminName = null
    ): bool {
        try {
            $session = session();
            $request = service('request');

            if ($adminId === null && $session) {
                $adminId = (int) $session->get('admin_id');
            }

            if ($adminName === null && $adminId) {
                $admin = (new AdminModel())->find($adminId);
                $adminName = $admin ? ($admin['name'] ?: $admin['username']) : '管理員 #' . $adminId;
            }

            $ip = '127.0.0.1';
            $userAgent = '';
            if ($request) {
                $rawIp = $request->getIPAddress();
                if ($rawIp && $rawIp !== '0.0.0.0') {
                    $ip = $rawIp;
                } elseif (!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '0.0.0.0') {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                $userAgent = substr((string) $request->getUserAgent(), 0, 255);
            }

            $data = [
                'admin_id'   => $adminId > 0 ? $adminId : null,
                'admin_name' => $adminName ?: '系統',
                'module'     => $module,
                'action'     => $action,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'status'     => $status,
                'details'    => $details,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            return (bool) (new self())->insert($data);
        } catch (\Throwable) {
            return false;
        }
    }
}
