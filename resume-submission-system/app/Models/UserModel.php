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
    protected $allowedFields    = ['student_id', 'name', 'email', 'password', 'file_name', 'file_content', 'uploaded_at'];

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

    public function applyAdminFilters(array $filters): self
    {
        $keyword   = trim((string) ($filters['keyword'] ?? ''));
        $searchBy  = $filters['search_by'] ?? 'name';
        $uploadStatus = $filters['upload_status'] ?? 'all';

        if ($keyword !== '') {
            $column = match ($searchBy) {
                'id'    => 'student_id',
                'email' => 'email',
                default => 'name',
            };

            $this->like($column, $keyword);
        }

        if ($uploadStatus === 'uploaded') {
            $this->where('file_name !=', null)
                ->where('file_name !=', '');
        } elseif ($uploadStatus === 'missing') {
            $this->groupStart()
                ->where('file_name', null)
                ->orWhere('file_name', '')
                ->groupEnd();
        }

        if (!empty($filters['uploaded_from'])) {
            $this->where('uploaded_at >=', $filters['uploaded_from'] . ' 00:00:00');
        }

        if (!empty($filters['uploaded_to'])) {
            $this->where('uploaded_at <=', $filters['uploaded_to'] . ' 23:59:59');
        }

        return $this;
    }

    public function getAdminStatistics(): array
    {
        $total = (new self())->countAll();
        $uploaded = (new self())
            ->where('file_name !=', null)
            ->where('file_name !=', '')
            ->countAllResults();

        $latest = (new self())
            ->selectMax('uploaded_at')
            ->where('file_name !=', null)
            ->where('file_name !=', '')
            ->first();

        return [
            'total'             => $total,
            'uploaded'          => $uploaded,
            'missing'           => $total - $uploaded,
            'latest_uploaded_at' => $latest['uploaded_at'] ?? null,
        ];
    }
}
