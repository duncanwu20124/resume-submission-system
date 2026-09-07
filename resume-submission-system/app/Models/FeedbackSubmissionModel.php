<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackSubmissionModel extends Model
{
    protected $table            = 'feedback_submissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'student_db_id',
        'status',
        'average_score',
        'submitted_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'student_db_id' => [
            'label' => '學生編號',
            'rules' => 'required|integer',
        ],
        'status' => [
            'label' => '回饋狀態',
            'rules' => 'required|in_list[draft,submitted]',
        ],
        'average_score' => [
            'label' => '平均評分',
            'rules' => 'permit_empty|decimal|greater_than_equal_to[1]|less_than_equal_to[5]',
        ],
        'submitted_at' => [
            'label' => '送出時間',
            'rules' => 'permit_empty|valid_date',
        ],
    ];

    protected $validationMessages = [
        'student_db_id' => [
            'required' => '找不到目前登入的學生資料。',
            'integer'  => '學生資料格式不正確。',
        ],
        'average_score' => [
            'decimal' => '平均分數格式不正確。',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * 取得學生的回饋主表資料。
     */
    public function findByStudentId(int $studentDbId): ?array
    {
        return $this
            ->where('student_db_id', $studentDbId)
            ->first();
    }
}