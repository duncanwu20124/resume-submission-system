<?php

namespace App\Models;

use CodeIgniter\Model;

class StudentScoreModel extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';

    protected $table = 'student_scores';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['student_db_id', 'total_score', 'status', 'comment', 'scored_by', 'confirmed_at'];
    protected $useTimestamps = true;

    public function findByStudent(int $studentId): ?array
    {
        return $this->where('student_db_id', $studentId)->first();
    }
}
