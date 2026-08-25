<?php

namespace App\Controllers;

use App\Models\SpaceModel;
use App\Models\SessionModel;
use App\Models\TicketModel;

/**
 * Dashboard Controller — main overview page.
 */
class Dashboard extends BaseController
{
    protected SpaceModel   $spaceModel;
    protected SessionModel $sessionModel;
    protected TicketModel  $ticketModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->spaceModel   = new SpaceModel();
        $this->sessionModel = new SessionModel();
        $this->ticketModel  = new TicketModel();
    }

    /**
     * Dashboard main page — shows stats and recent activity.
     */
    public function index(): string
    {
        $userId = session()->get('user_id');

        $data = [
            'title'           => 'Dashboard',
            'sidebar_tree'    => $this->spaceModel->getSidebarTree($userId),
            'pending_tickets' => $this->ticketModel->getTodayPendingCount($userId),
            'sessions_today'  => $this->sessionModel->getTodaySessionCount($userId),
            'recent_sessions' => $this->sessionModel->getRecentSessions($userId, 5),
            'active_session'  => $this->sessionModel->getActiveSession($userId),
            'spaces_count'    => count($this->spaceModel->where('user_id', $userId)->findAll()),
        ];

        return view('dashboard/index', $data);
    }
}
