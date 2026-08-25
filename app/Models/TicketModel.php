<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TicketModel — manages Tickets (tasks) within Projects.
 * Implements Eisenhower priority matrix filtering.
 */
class TicketModel extends Model
{
    protected $table          = 'tickets';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'project_id', 'title', 'description', 'priority', 'status',
    ];

    protected $validationRules = [
        'project_id'  => 'required|integer',
        'title'       => 'required|min_length[2]|max_length[200]',
        'priority'    => 'required|in_list[urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important]',
        'status'      => 'required|in_list[pending,ongoing,done,waiting-approval,cancelled]',
    ];

    // Eisenhower priority quadrants (Q1–Q4)
    public const PRIORITIES = [
        'urgent-important'         => 'Q1 – Urgent & Important',
        'important-not-urgent'     => 'Q2 – Important, Not Urgent',
        'urgent-not-important'     => 'Q3 – Urgent, Not Important',
        'not-urgent-not-important' => 'Q4 – Not Urgent, Not Important',
    ];

    // Priority → Bootstrap color class
    public const PRIORITY_COLORS = [
        'urgent-important'         => 'danger',
        'important-not-urgent'     => 'warning',
        'urgent-not-important'     => 'info',
        'not-urgent-not-important' => 'secondary',
    ];

    // Status → Bootstrap color class
    public const STATUS_COLORS = [
        'pending'          => 'secondary',
        'ongoing'          => 'primary',
        'done'             => 'success',
        'waiting-approval' => 'warning',
        'cancelled'        => 'danger',
    ];

    // ----------------------------------------------------------------
    // Query Methods
    // ----------------------------------------------------------------

    /**
     * Get all tickets for a project, optionally filtered by priority.
     *
     * @param  int         $projectId
     * @param  string|null $priority  One of the PRIORITIES keys, or null for all
     * @param  string|null $status    Filter by status too
     */
    public function getTicketsByProject(
        int $projectId,
        ?string $priority = null,
        ?string $status = null
    ): array {
        $builder = $this->where('project_id', $projectId);

        if ($priority && array_key_exists($priority, self::PRIORITIES)) {
            $builder = $builder->where('priority', $priority);
        }

        if ($status && in_array($status, ['pending', 'ongoing', 'done', 'waiting-approval', 'cancelled'])) {
            $builder = $builder->where('status', $status);
        }

        // Order: by priority severity (Q1 first) then by created_at
        $priorityOrder = implode(',', array_map(
            fn($p) => "'" . $p . "'",
            array_keys(self::PRIORITIES)
        ));

        return $builder
            ->orderBy("FIELD(priority, {$priorityOrder})", '', false)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get ticket counts per status for a project.
     */
    public function getTicketStats(int $projectId): array
    {
        $rows = $this->db->table('tickets')
            ->select('status, COUNT(*) AS count')
            ->where('project_id', $projectId)
            ->groupBy('status')
            ->get()
            ->getResultArray();

        $stats = array_fill_keys(['pending', 'ongoing', 'done', 'waiting-approval', 'cancelled', 'total'], 0);

        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }

        return $stats;
    }

    /**
     * Get today's pending tickets for a user (dashboard widget).
     */
    public function getTodayPendingCount(int $userId): int
    {
        return $this->db->table('tickets t')
            ->join('projects p', 'p.id = t.project_id')
            ->join('spaces s', 's.id = p.space_id')
            ->where('s.user_id', $userId)
            ->whereIn('t.status', ['pending', 'ongoing'])
            ->countAllResults();
    }

    /**
     * Get tickets not in a terminal state for a project (for active sessions).
     */
    public function getActiveTicketsForProject(int $projectId): array
    {
        return $this->where('project_id', $projectId)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Update only the status field of a ticket.
     */
    public function updateStatus(int $ticketId, string $status): bool
    {
        return $this->update($ticketId, ['status' => $status]);
    }
}
