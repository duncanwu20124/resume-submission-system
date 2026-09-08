<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackAnswerModel extends Model
{
    protected $table            = 'feedback_answers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'feedback_submission_id',
        'question_key',
        'question_number',
        'question_text',
        'answer_type',
        'rating_value',
        'text_value',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'feedback_submission_id' => [
            'label' => '回饋表編號',
            'rules' => 'required|integer',
        ],
        'question_key' => [
            'label' => '題目代碼',
            'rules' => 'required|max_length[80]|alpha_dash',
        ],
        'question_number' => [
            'label' => '題號',
            'rules' => 'required|integer|greater_than_equal_to[1]',
        ],
        'question_text' => [
            'label' => '題目內容',
            'rules' => 'required',
        ],
        'answer_type' => [
            'label' => '回答類型',
            'rules' => 'required|in_list[rating,text]',
        ],
        'rating_value' => [
            'label' => '評分',
            'rules' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[5]',
        ],
        'text_value' => [
            'label' => '文字回答',
            'rules' => 'permit_empty|max_length[500]',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * 取得某份回饋的所有答案，按照題號排列。
     */
    public function findBySubmissionId(int $submissionId): array
    {
        return $this
            ->where('feedback_submission_id', $submissionId)
            ->orderBy('question_number', 'ASC')
            ->findAll();
    }

    /**
     * 刪除某份回饋原有的所有答案。
     *
     * 學生重新送出問卷時，會先刪除舊答案，
     * 再寫入最新的25題回答。
     */
    public function deleteBySubmissionId(int $submissionId): bool
    {
        return (bool) $this
            ->where('feedback_submission_id', $submissionId)
            ->delete();
    }
}