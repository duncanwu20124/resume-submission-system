<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFilesToStudents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('students', [
            'file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'password', // 加在密碼欄位後面
            ],
            'uploaded_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'file_name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('students', ['file_name', 'uploaded_at']);
    }
}
