<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'students';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['student_id', 'name', 'email', 'password', 'file_name', 'uploaded_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function searchByUserId($student_id)
    {
        return $this->like('student_id', $student_id)->findAll();
    }

    public function searchByName($name)
    {
        return $this->like('name', $name)->findAll();
    }

    public function searchByEmail($email)
    {
        return $this->like('email', $email)->findAll();
    }
}
