<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResetCodeToStudents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('students', [
            'reset_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
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
        try {
            $this->forge->dropColumn('students', 'reset_code');
        } catch (\Throwable $e) {
        }
        try {
            $this->forge->dropColumn('students', 'reset_expires_at');
        } catch (\Throwable $e) {
        }
    }
}
