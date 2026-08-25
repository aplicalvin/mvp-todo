<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ProjectModel — manages Projects within Spaces.
 */
class ProjectModel extends Model
{
    protected $table          = 'projects';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'space_id', 'name', 'description', 'type',
    ];

    protected $validationRules = [
        'space_id' => 'required|integer',
        'name'     => 'required|min_length[2]|max_length[100]',
        'type'     => 'required|in_list[routine,seasonal]',
    ];

    // ----------------------------------------------------------------
    // Query Methods
    // ----------------------------------------------------------------

    /**
     * Get all projects for a space, with ticket statistics per status.
     */
    public function getProjectsBySpace(int $spaceId): array
    {
        $projects = $this->where('space_id', $spaceId)->orderBy('name', 'ASC')->findAll();

        foreach ($projects as &$project) {
            $project['ticket_stats'] = $this->getTicketStats($project['id']);
        }

        return $projects;
    }

    /**
     * Get a project with its parent space info (for breadcrumbs).
     */
    public function getProjectWithSpace(int $projectId): ?array
    {
        return $this->db->table('projects p')
            ->select('p.*, s.name AS space_name, s.id AS space_id_check')
            ->join('spaces s', 's.id = p.space_id')
            ->where('p.id', $projectId)
            ->get()
            ->getRowArray();
    }

    /**
     * Get ticket counts grouped by status for a project.
     */
    public function getTicketStats(int $projectId): array
    {
        $rows = $this->db->table('tickets')
            ->select('status, COUNT(*) AS count')
            ->where('project_id', $projectId)
            ->groupBy('status')
            ->get()
            ->getResultArray();

        // Build a flat map: status => count
        $stats = [
            'pending'          => 0,
            'ongoing'          => 0,
            'done'             => 0,
            'waiting-approval' => 0,
            'cancelled'        => 0,
            'total'            => 0,
        ];

        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }

        return $stats;
    }

    /**
     * Get projects for a space scoped through user (security check).
     */
    public function getProjectsForUser(int $spaceId, int $userId): array
    {
        return $this->db->table('projects p')
            ->select('p.*')
            ->join('spaces s', 's.id = p.space_id')
            ->where('p.space_id', $spaceId)
            ->where('s.user_id', $userId)
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Verify a project belongs to the given user (via space).
     */
    public function belongsToUser(int $projectId, int $userId): bool
    {
        return $this->db->table('projects p')
            ->join('spaces s', 's.id = p.space_id')
            ->where('p.id', $projectId)
            ->where('s.user_id', $userId)
            ->countAllResults() > 0;
    }
}
