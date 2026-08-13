<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToAdmins extends Migration
{
    public function up()
    {
        $this->forge->addColumn('admins', [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'after'      => 'username',
            ],
            'employee_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'after'      => 'email',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('admins', ['email', 'employee_id']);
    }
}
