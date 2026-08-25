<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SpaceModel — manages Spaces (top-level containers).
 * Scoped per user_id for all queries.
 */
class SpaceModel extends Model
{
    protected $table          = 'spaces';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'name', 'description', 'user_id',
    ];

    protected $validationRules = [
        'name'    => 'required|min_length[2]|max_length[100]',
        'user_id' => 'required|integer',
    ];

    // ----------------------------------------------------------------
    // Query Methods
    // ----------------------------------------------------------------

    /**
     * Get all spaces for a given user, including project count.
     */
    public function getSpacesWithProjectCount(int $userId): array
    {
        return $this->db->table('spaces s')
            ->select('s.*, COUNT(p.id) AS project_count')
            ->join('projects p', 'p.space_id = s.id', 'left')
            ->where('s.user_id', $userId)
            ->groupBy('s.id')
            ->orderBy('s.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get a single space with all its projects (for sidebar tree).
     */
    public function getSpaceWithProjects(int $spaceId, int $userId): ?array
    {
        $space = $this->where('id', $spaceId)->where('user_id', $userId)->first();
        if (! $space) {
            return null;
        }

        $space['projects'] = $this->db->table('projects')
            ->where('space_id', $spaceId)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        return $space;
    }

    /**
     * Get full sidebar tree: spaces → projects for a user.
     */
    public function getSidebarTree(int $userId): array
    {
        $spaces = $this->where('user_id', $userId)->orderBy('name', 'ASC')->findAll();

        foreach ($spaces as &$space) {
            $space['projects'] = $this->db->table('projects')
                ->where('space_id', $space['id'])
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();
        }

        return $spaces;
    }

    /**
     * Check ownership — verify a space belongs to the given user.
     */
    public function belongsToUser(int $spaceId, int $userId): bool
    {
        return $this->where('id', $spaceId)->where('user_id', $userId)->countAllResults() > 0;
    }
}
