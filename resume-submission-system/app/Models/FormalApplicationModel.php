<?php

namespace App\Models;

use App\Support\FormalApplicationData;
use CodeIgniter\Model;

class FormalApplicationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'formal_applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['student_id', 'name'];
    protected $useTimestamps    = false;

    public function ensureInitialData(): void
    {
        $existingRows = $this->select('student_id')->findAll();
        $existingIds = array_fill_keys(array_column($existingRows, 'student_id'), true);
        $missingRows = [];

        foreach (FormalApplicationData::all() as $application) {
            if (!isset($existingIds[$application['student_id']])) {
                $missingRows[] = $application;
            }
        }

        if ($missingRows === []) {
            return;
        }

        $this->builder()->ignore(true)->insertBatch($missingRows);
    }
}
