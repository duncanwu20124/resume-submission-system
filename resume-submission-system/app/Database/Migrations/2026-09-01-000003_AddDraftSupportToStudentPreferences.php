<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDraftSupportToStudentPreferences extends Migration
{
    public function up()
    {
        $this->forge->addColumn('student_preferences', [
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'draft',
                'after'      => 'choice_6',
            ],
            'submitted_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'status',
            ],
        ]);

        // Every row that existed before this migration was created under the
        // old "row exists = submitted" model, so backfill it as a real submission.
        $table = $this->db->prefixTable('student_preferences');
        $this->db->query(
            "UPDATE {$table} SET status = 'submitted', submitted_at = created_at WHERE status = 'draft'"
        );
    }

    public function down()
    {
        try {
            $this->forge->dropColumn('student_preferences', 'status');
        } catch (\Throwable $e) {
        }
        try {
            $this->forge->dropColumn('student_preferences', 'submitted_at');
        } catch (\Throwable $e) {
        }
    }
}
