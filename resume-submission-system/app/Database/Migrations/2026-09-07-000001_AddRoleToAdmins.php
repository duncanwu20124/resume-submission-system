<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToAdmins extends Migration
{
    public function up()
    {
        $this->forge->addColumn('admins', [
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'null'       => false,
                'default'    => 'reviewer',
                'after'      => 'password',
            ],
        ]);

        $adminsTable = $this->db->prefixTable('admins');

        // 將既有主要管理員 (admin 或 id=1) 設為 super_admin，其餘既有管理員設為 admin
        $this->db->query("UPDATE {$adminsTable} SET role = 'admin' WHERE role = 'reviewer'");
        $this->db->query("UPDATE {$adminsTable} SET role = 'super_admin' WHERE username = 'admin' OR admin_id = 1");
    }

    public function down()
    {
        $this->forge->dropColumn('admins', 'role');
    }
}
