<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdmins extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'admin_id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
        ]);
        $this->forge->addKey('admin_id', true);
        $this->forge->createTable('admins');
    }

    public function down()
    {
        $this->forge->dropTable('admins');
    }
}
