<?php

namespace App\Controllers;

use App\Models\SpaceModel;
use App\Models\ProjectModel;
use App\Models\TicketModel;
use App\Models\SessionModel;
use App\Models\SessionTicketModel;

/**
 * Session Controller — manages Focus Sessions (Pomodoro-based work sessions).
 */
class Session extends BaseController
{
    protected SpaceModel         $spaceModel;
    protected ProjectModel       $projectModel;
    protected TicketModel        $ticketModel;
    protected SessionModel       $sessionModel;
    protected SessionTicketModel $sessionTicketModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->spaceModel         = new SpaceModel();
        $this->projectModel       = new ProjectModel();
        $this->ticketModel        = new TicketModel();
        $this->sessionModel       = new SessionModel();
        $this->sessionTicketModel = new SessionTicketModel();
    }

    // ----------------------------------------------------------------
    // Recap / History
    // ----------------------------------------------------------------

    /**
     * GET /sessions — Show all sessions for the user (recap/history).
     */
    public function index(): string
    {
        $userId = session()->get('user_id');

        return view('sessions/index', [
            'title'        => 'Riwayat Sesi',
            'sidebar_tree' => $this->spaceModel->getSidebarTree($userId),
            'sessions'     => $this->sessionModel->getSessionsByUser($userId),
        ]);
    }

    // ----------------------------------------------------------------
    // Create Session
    // ----------------------------------------------------------------

    /**
     * GET /sessions/create — Show session creation form.
     */
    public function create(): string
    {
        $userId = session()->get('user_id');
        $spaces = $this->spaceModel->where('user_id', $userId)->orderBy('name', 'ASC')->findAll();

        return view('sessions/create', [
            'title'        => 'Mulai Sesi Fokus',
            'sidebar_tree' => $this->spaceModel->getSidebarTree($userId),
            'spaces'       => $spaces,
        ]);
    }

    /**
     * POST /sessions — Create and start a new session.
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $userId    = session()->get('user_id');
        $projectId = (int) $this->request->getPost('project_id');

        // Validate
        if (! $this->projectModel->belongsToUser($projectId, $userId)) {
            return redirect()->back()->with('error', 'Proyek tidak valid.');
        }

        // Check for already-active session
        $active = $this->sessionModel->getActiveSession($userId);
        if ($active) {
            return redirect()->to("/sessions/{$active['id']}/active")
                ->with('warning', 'Kamu masih memiliki sesi aktif. Lanjutkan atau akhiri dulu.');
        }

        // Create the session
        $sessionId = $this->sessionModel->insert([
            'project_id' => $projectId,
            'user_id'    => $userId,
            'start_time' => date('Y-m-d H:i:s'),
            'status'     => 'active',
        ]);

        if (! $sessionId) {
            return redirect()->back()->with('error', 'Gagal memulai sesi.');
        }

        // Auto-populate session_tickets with active tickets from the project
        $tickets = $this->ticketModel->getActiveTicketsForProject($projectId);
        $ticketIds = array_column($tickets, 'id');
        $this->sessionTicketModel->addTicketsToSession($sessionId, $ticketIds);

        return redirect()->to("/sessions/{$sessionId}/active")
            ->with('success', 'Sesi dimulai!');
    }

    // ----------------------------------------------------------------
    // Active Session View
    // ----------------------------------------------------------------

    /**
     * GET /sessions/{id}/active — Show the active session split-panel view.
     */
    public function active(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $userId = session()->get('user_id');

        if (! $this->sessionModel->belongsToUser($id, $userId)) {
            return redirect()->to('/sessions')->with('error', 'Sesi tidak ditemukan.');
        }

        $sessionData = $this->sessionModel->getSessionWithDetails($id);

        if (! $sessionData || $sessionData['status'] !== 'active') {
            return redirect()->to("/sessions/{$id}")
                ->with('info', 'Sesi ini sudah berakhir.');
        }

        $sessionTickets = $this->sessionTicketModel->getTicketsForSession($id);

        return view('sessions/active', [
            'title'          => 'Sesi Aktif: ' . $sessionData['project_name'],
            'session'        => $sessionData,
            'sessionTickets' => $sessionTickets,
            'priorityColors' => TicketModel::PRIORITY_COLORS,
            'statusColors'   => TicketModel::STATUS_COLORS,
        ]);
    }

    // ----------------------------------------------------------------
    // End Session
    // ----------------------------------------------------------------

    /**
     * POST /sessions/{id}/end — End a session, save stats, return summary JSON.
     */
    public function end(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');

        if (! $this->sessionModel->belongsToUser($id, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesi tidak ditemukan.',
            ])->setStatusCode(403);
        }

        $sessionData = $this->sessionModel->find($id);

        if ($sessionData['status'] !== 'active') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesi sudah berakhir.',
            ])->setStatusCode(400);
        }

        $endTime      = date('Y-m-d H:i:s');
        $startTime    = strtotime($sessionData['start_time']);
        $durationSecs = time() - $startTime;
        $durationMins = max(1, (int) round($durationSecs / 60));

        // Save end data
        $this->sessionModel->endSession($id, $endTime, $durationMins);

        // Get completed tickets for summary
        $completedTickets = $this->sessionTicketModel->getTicketsForSession($id);
        $done = array_filter($completedTickets, fn($t) => $t['status'] === 'completed');

        return $this->response->setJSON([
            'success'          => true,
            'message'          => 'Sesi berhasil diakhiri.',
            'duration_minutes' => $durationMins,
            'tickets_done'     => count($done),
            'tickets'          => array_values($done),
            'redirect_url'     => base_url("/sessions/{$id}"),
        ]);
    }

    // ----------------------------------------------------------------
    // AJAX — Tick a Ticket
    // ----------------------------------------------------------------

    /**
     * POST /sessions/tick — Mark/unmark a ticket as done in a session.
     */
    public function tickTicket(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId    = session()->get('user_id');
        $sessionId = (int) $this->request->getPost('session_id');
        $ticketId  = (int) $this->request->getPost('ticket_id');
        $done      = $this->request->getPost('done') === '1';

        // Validate session ownership
        if (! $this->sessionModel->belongsToUser($sessionId, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesi tidak valid.',
            ])->setStatusCode(403);
        }

        if ($done) {
            // Mark complete and update the ticket's global status to "done"
            $this->sessionTicketModel->markTicketComplete($sessionId, $ticketId);
            $this->ticketModel->updateStatus($ticketId, 'done');
        } else {
            // Unmark
            $this->sessionTicketModel->markTicketPending($sessionId, $ticketId);
            $this->ticketModel->updateStatus($ticketId, 'ongoing');
        }

        return $this->response->setJSON([
            'success' => true,
            'done'    => $done,
            'message' => $done ? 'Tiket ditandai selesai.' : 'Tiket ditandai belum selesai.',
        ]);
    }

    // ----------------------------------------------------------------
    // Session Detail
    // ----------------------------------------------------------------

    /**
     * GET /sessions/{id} — Show session detail/recap.
     */
    public function detail(int $id): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $userId = session()->get('user_id');

        if (! $this->sessionModel->belongsToUser($id, $userId)) {
            return redirect()->to('/sessions')->with('error', 'Sesi tidak ditemukan.');
        }

        $sessionData    = $this->sessionModel->getSessionWithDetails($id);
        $sessionTickets = $this->sessionTicketModel->getTicketsForSession($id);

        return view('sessions/detail', [
            'title'          => 'Detail Sesi',
            'sidebar_tree'   => $this->spaceModel->getSidebarTree($userId),
            'session'        => $sessionData,
            'sessionTickets' => $sessionTickets,
            'priorityColors' => TicketModel::PRIORITY_COLORS,
            'statusColors'   => TicketModel::STATUS_COLORS,
        ]);
    }
}
