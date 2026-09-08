<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'admins';
    protected $primaryKey       = 'admin_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_REVIEWER = 'reviewer';

    public static function roleLabels(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => '超級管理員',
            self::ROLE_ADMIN => '一般管理員',
            self::ROLE_REVIEWER => '審查委員',
        ];
    }

    public static function validRoles(): array
    {
        return [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_REVIEWER];
    }

    protected $allowedFields = ['name', 'username', 'password', 'role', 'email', 'employee_id', 'reset_token', 'reset_expires_at'];

    protected $useTimestamps = false;

    public function findByUsername($username)
    {
        return $this->where('username', $username)->first();
    }

    public function countSuperAdmins(): int
    {
        return $this->where('role', self::ROLE_SUPER_ADMIN)->countAllResults();
    }
}
