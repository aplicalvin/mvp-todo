<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SessionModel — manages Focus Sessions.
 */
class SessionModel extends Model
{
    protected $table          = 'sessions';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'project_id', 'user_id', 'start_time', 'end_time', 'duration_minutes', 'status',
    ];

    protected $validationRules = [
        'project_id' => 'required|integer',
        'user_id'    => 'required|integer',
        'start_time' => 'required',
        'status'     => 'required|in_list[active,completed,cancelled]',
    ];

    // ----------------------------------------------------------------
    // Query Methods
    // ----------------------------------------------------------------

    /**
     * Get session with project and space info (for detail/breadcrumbs).
     */
    public function getSessionWithDetails(int $sessionId): ?array
    {
        return $this->db->table('sessions s')
            ->select('s.*, p.name AS project_name, p.type AS project_type, sp.name AS space_name, sp.id AS space_id')
            ->join('projects p', 'p.id = s.project_id')
            ->join('spaces sp', 'sp.id = p.space_id')
            ->where('s.id', $sessionId)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all sessions for a user, ordered by most recent first.
     */
    public function getSessionsByUser(int $userId, int $limit = 50): array
    {
        return $this->db->table('sessions s')
            ->select('s.*, p.name AS project_name, p.type AS project_type, sp.name AS space_name,
                      (SELECT COUNT(*) FROM session_tickets st WHERE st.session_id = s.id AND st.status = "completed") AS tickets_done')
            ->join('projects p', 'p.id = s.project_id')
            ->join('spaces sp', 'sp.id = p.space_id')
            ->where('s.user_id', $userId)
            ->orderBy('s.start_time', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get recent sessions for the dashboard widget.
     */
    public function getRecentSessions(int $userId, int $limit = 5): array
    {
        return $this->db->table('sessions s')
            ->select('s.*, p.name AS project_name, sp.name AS space_name,
                      (SELECT COUNT(*) FROM session_tickets st WHERE st.session_id = s.id AND st.status = "completed") AS tickets_done')
            ->join('projects p', 'p.id = s.project_id')
            ->join('spaces sp', 'sp.id = p.space_id')
            ->where('s.user_id', $userId)
            ->whereIn('s.status', ['completed', 'active'])
            ->orderBy('s.start_time', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Count today's sessions for a user.
     */
    public function getTodaySessionCount(int $userId): int
    {
        return $this->db->table('sessions')
            ->where('user_id', $userId)
            ->where('DATE(start_time)', date('Y-m-d'))
            ->whereNotIn('status', ['cancelled'])
            ->countAllResults();
    }

    /**
     * Get any currently active session for a user.
     */
    public function getActiveSession(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('start_time', 'DESC')
            ->first();
    }

    /**
     * End a session: set end_time, duration, and status.
     */
    public function endSession(int $sessionId, string $endTime, int $durationMinutes): bool
    {
        return $this->update($sessionId, [
            'end_time'         => $endTime,
            'duration_minutes' => $durationMinutes,
            'status'           => 'completed',
        ]);
    }

    /**
     * Verify session ownership.
     */
    public function belongsToUser(int $sessionId, int $userId): bool
    {
        return $this->where('id', $sessionId)->where('user_id', $userId)->countAllResults() > 0;
    }
}
