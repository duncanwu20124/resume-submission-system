<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameToAdmins extends Migration
{
    public function up()
    {
        $this->forge->addColumn('admins', [
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'default'    => '',
                'after'      => 'admin_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('admins', 'name');
    }
}
