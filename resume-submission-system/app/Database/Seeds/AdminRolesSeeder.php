<?php

namespace App\Database\Seeds;

use App\Models\AdminModel;
use CodeIgniter\Database\Seeder;

class AdminRolesSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();
        $table = $db->table('admins');

        $commonPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);

        $accounts = [
            [
                'username'    => 'super_admin',
                'name'        => '超級管理員',
                'email'       => 'super_admin@system.local',
                'employee_id' => 'SA001',
                'role'        => AdminModel::ROLE_SUPER_ADMIN,
                'password'    => $commonPasswordHash,
            ],
            [
                'username'    => 'admin',
                'name'        => '管理員 (超級管理員)',
                'email'       => 'admin@system.local',
                'employee_id' => 'SA002',
                'role'        => AdminModel::ROLE_SUPER_ADMIN,
                'password'    => $commonPasswordHash,
            ],
            [
                'username'    => 'admin_user',
                'name'        => '一般管理員',
                'email'       => 'admin_user@system.local',
                'employee_id' => 'AD001',
                'role'        => AdminModel::ROLE_ADMIN,
                'password'    => $commonPasswordHash,
            ],
            [
                'username'    => 'admin01',
                'name'        => '一般管理員 01',
                'email'       => 'admin01@system.local',
                'employee_id' => 'AD002',
                'role'        => AdminModel::ROLE_ADMIN,
                'password'    => $commonPasswordHash,
            ],
            [
                'username'    => 'reviewer',
                'name'        => '審查委員',
                'email'       => 'reviewer@system.local',
                'employee_id' => 'RV001',
                'role'        => AdminModel::ROLE_REVIEWER,
                'password'    => $commonPasswordHash,
            ],
        ];

        foreach ($accounts as $account) {
            $existing = $table->where('username', $account['username'])->get()->getRowArray();
            if ($existing) {
                $table->where('admin_id', $existing['admin_id'])->update([
                    'name'        => $account['name'],
                    'email'       => $account['email'],
                    'employee_id' => $account['employee_id'],
                    'role'        => $account['role'],
                    'password'    => $account['password'],
                ]);
            } else {
                $table->insert($account);
            }
        }
    }
}
