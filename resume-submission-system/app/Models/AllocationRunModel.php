<?php

namespace App\Models;

use CodeIgniter\Model;

class AllocationRunModel extends Model
{
    public const STATUS_PREVIEW = 'preview';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_SUPERSEDED = 'superseded';

    protected $table = 'allocation_runs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['status', 'random_seed', 'started_by', 'started_at', 'published_at', 'revision_note'];
    protected $useTimestamps = true;

    public function latestPublished(): ?array
    {
        return $this->where('status', self::STATUS_PUBLISHED)->orderBy('published_at', 'DESC')->first();
    }
}
