<?php

namespace App\Database\Migrations;

use App\Config\Universities;
use CodeIgniter\Database\Migration;

class CreateScoringAndAllocationTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INTEGER', 'auto_increment' => true],
            'student_db_id'=> ['type' => 'INTEGER', 'unsigned' => true],
            'total_score'  => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'comment'      => ['type' => 'TEXT', 'null' => true],
            'scored_by'    => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'confirmed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('student_db_id');
        $this->forge->createTable('student_scores');

        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'capacity'   => ['type' => 'INTEGER', 'unsigned' => true, 'default' => 1],
            'is_active'  => ['type' => 'INTEGER', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('university_capacities');

        $now = date('Y-m-d H:i:s');
        foreach (Universities::names() as $name) {
            $this->db->table('university_capacities')->insert([
                'name' => $name, 'capacity' => 1, 'is_active' => 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $this->forge->addField([
            'id'            => ['type' => 'INTEGER', 'auto_increment' => true],
            'status'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'preview'],
            'random_seed'   => ['type' => 'VARCHAR', 'constraint' => 64],
            'started_by'    => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'started_at'    => ['type' => 'DATETIME'],
            'published_at'  => ['type' => 'DATETIME', 'null' => true],
            'revision_note' => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('allocation_runs');

        $this->forge->addField([
            'id'                       => ['type' => 'INTEGER', 'auto_increment' => true],
            'allocation_run_id'        => ['type' => 'INTEGER', 'unsigned' => true],
            'student_db_id'            => ['type' => 'INTEGER', 'unsigned' => true],
            'score_snapshot'           => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'lottery_order'            => ['type' => 'VARCHAR', 'constraint' => 64],
            'overall_rank'             => ['type' => 'INTEGER', 'unsigned' => true],
            'university_name_snapshot' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'preference_rank'          => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'result_status'            => ['type' => 'VARCHAR', 'constraint' => 20],
            'reason'                   => ['type' => 'TEXT', 'null' => true],
            'created_at'               => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['allocation_run_id', 'student_db_id']);
        $this->forge->addKey(['allocation_run_id', 'overall_rank']);
        $this->forge->createTable('allocation_results');
    }

    public function down()
    {
        $this->forge->dropTable('allocation_results', true);
        $this->forge->dropTable('allocation_runs', true);
        $this->forge->dropTable('university_capacities', true);
        $this->forge->dropTable('student_scores', true);
    }
}
