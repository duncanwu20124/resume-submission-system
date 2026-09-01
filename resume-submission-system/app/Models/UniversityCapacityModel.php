<?php

namespace App\Models;

use CodeIgniter\Model;

class UniversityCapacityModel extends Model
{
    protected $table = 'university_capacities';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'capacity', 'is_active'];
    protected $useTimestamps = true;
}
