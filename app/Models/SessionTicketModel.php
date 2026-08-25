<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SessionTicketModel — manages the session_tickets junction table.
 * Tracks which tickets were completed during a session.
 */
class SessionTicketModel extends Model
{
    protected $table          = 'session_tickets';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false; // No timestamps on junction table

    protected $allowedFields = [
        'session_id', 'ticket_id', 'status', 'completed_at',
    ];

    // ----------------------------------------------------------------
    // Query Methods
    // ----------------------------------------------------------------

    /**
     * Get all tickets linked to a session, joined with ticket data.
     */
    public function getTicketsForSession(int $sessionId): array
    {
        return $this->db->table('session_tickets st')
            ->select('st.*, t.title, t.description, t.priority, t.status AS ticket_status, t.project_id')
            ->join('tickets t', 't.id = st.ticket_id')
            ->where('st.session_id', $sessionId)
            ->orderBy('t.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mark a session ticket as completed, recording the completion time.
     */
    public function markTicketComplete(int $sessionId, int $ticketId): bool
    {
        // Check if record exists
        $existing = $this->where('session_id', $sessionId)
            ->where('ticket_id', $ticketId)
            ->first();

        if (! $existing) {
            // Insert new record as completed
            return $this->insert([
                'session_id'   => $sessionId,
                'ticket_id'    => $ticketId,
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ]) !== false;
        }

        // Update existing to completed
        return $this->where('session_id', $sessionId)
            ->where('ticket_id', $ticketId)
            ->set([
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }

    /**
     * Mark a session ticket back to pending (un-check).
     */
    public function markTicketPending(int $sessionId, int $ticketId): bool
    {
        return $this->where('session_id', $sessionId)
            ->where('ticket_id', $ticketId)
            ->set([
                'status'       => 'pending',
                'completed_at' => null,
            ])
            ->update();
    }

    /**
     * Bulk insert tickets into a session (called when session is created).
     */
    public function addTicketsToSession(int $sessionId, array $ticketIds): void
    {
        $rows = [];
        foreach ($ticketIds as $ticketId) {
            $rows[] = [
                'session_id' => $sessionId,
                'ticket_id'  => (int) $ticketId,
                'status'     => 'pending',
                'completed_at' => null,
            ];
        }
        if (! empty($rows)) {
            $this->insertBatch($rows);
        }
    }

    /**
     * Get count of completed tickets for a session.
     */
    public function getCompletedCount(int $sessionId): int
    {
        return $this->where('session_id', $sessionId)
            ->where('status', 'completed')
            ->countAllResults();
    }

    /**
     * Check if a specific ticket is already marked complete in a session.
     */
    public function isTicketComplete(int $sessionId, int $ticketId): bool
    {
        $row = $this->where('session_id', $sessionId)
            ->where('ticket_id', $ticketId)
            ->first();

        return $row && $row['status'] === 'completed';
    }
}
