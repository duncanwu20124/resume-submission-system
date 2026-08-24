<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResetPasswordToAdmins extends Migration
{
    public function up()
    {
        $this->forge->addColumn('admins', [
            'reset_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'reset_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('admins', ['reset_token', 'reset_expires_at']);
    }
}
