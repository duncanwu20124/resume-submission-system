<?php

namespace App\Models;

use CodeIgniter\Model;

class AllocationResultModel extends Model
{
    protected $table = 'allocation_results';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['allocation_run_id', 'student_db_id', 'score_snapshot', 'lottery_order', 'overall_rank', 'university_name_snapshot', 'preference_rank', 'result_status', 'reason', 'created_at'];
    protected $useTimestamps = false;
}
