<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudentPreferencesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 9, 'unsigned' => true, 'auto_increment' => true],
            'student_db_id' => ['type' => 'INT', 'constraint' => 9, 'unsigned' => true, 'null' => false],
            'choice_1' => ['type' => 'VARCHAR', 'constraint' => '150', 'null' => false],
            'choice_2' => ['type' => 'VARCHAR', 'constraint' => '150', 'null' => false],
            'choice_3' => ['type' => 'VARCHAR', 'constraint' => '150', 'null' => false],
            'choice_4' => ['type' => 'VARCHAR', 'constraint' => '150', 'null' => false],
            'choice_5' => ['type' => 'VARCHAR', 'constraint' => '150', 'null' => false],
            'choice_6' => ['type' => 'VARCHAR', 'constraint' => '150', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('student_db_id');
        $this->forge->createTable('student_preferences');
    }

    public function down()
    {
        $this->forge->dropTable('student_preferences');
    }
}
