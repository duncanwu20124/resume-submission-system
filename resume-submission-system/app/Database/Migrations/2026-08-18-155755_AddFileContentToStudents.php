<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFileContentToStudents extends Migration
{
    public function up()
    {
        $this->forge->addColumn('students', [
            'file_content' => [
                'type' => 'BLOB',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('students', 'file_content');
    }
}
