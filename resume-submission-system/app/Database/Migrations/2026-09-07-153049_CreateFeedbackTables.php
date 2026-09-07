<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFeedbackTables extends Migration
{
    public function up()
    {
        /*
         * 回饋表主表
         * 每位學生保留一份回饋資料。
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'student_db_id' => [
                'type'       => 'INTEGER',
                'unsigned'   => true,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'submitted',
            ],
            'average_score' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,2',
                'null'       => true,
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        // 每位學生只保留一份問卷
        $this->forge->addUniqueKey(
            'student_db_id',
            'feedback_submissions_student_db_id'
        );

        $this->forge->createTable('feedback_submissions', true);

        /*
         * 各題回答明細
         * 每個提交紀錄可以有多筆題目回答。
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'feedback_submission_id' => [
                'type'       => 'INTEGER',
                'unsigned'   => true,
                'null'       => false,
            ],
            'question_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => false,
            ],
            'question_number' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
                'null'     => false,
            ],
            'question_text' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'answer_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'rating_value' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'text_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('feedback_submission_id');
        $this->forge->addKey('question_key');

        // 同一份回饋中，同一題只能有一筆回答
        $this->forge->addUniqueKey(
            ['feedback_submission_id', 'question_key'],
            'feedback_answers_submission_question'
        );

        $this->forge->addForeignKey(
            'feedback_submission_id',
            'feedback_submissions',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('feedback_answers', true);
    }

    public function down()
    {
        // 必須先刪除明細表，再刪除主表
        $this->forge->dropTable('feedback_answers', true);
        $this->forge->dropTable('feedback_submissions', true);
    }
}