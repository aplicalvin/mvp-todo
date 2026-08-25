<?php

namespace App\Controllers;

use App\Models\SpaceModel;
use App\Models\ProjectModel;
use App\Models\TicketModel;

/**
 * Ticket Controller — CRUD for Tickets within Projects.
 * Supports Eisenhower priority filtering and AJAX status updates.
 */
class Ticket extends BaseController
{
    protected SpaceModel   $spaceModel;
    protected ProjectModel $projectModel;
    protected TicketModel  $ticketModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->spaceModel   = new SpaceModel();
        $this->projectModel = new ProjectModel();
        $this->ticketModel  = new TicketModel();
    }

    // ----------------------------------------------------------------
    // Page Views
    // ----------------------------------------------------------------

    /**
     * GET /projects/{project_id}/tickets — List tickets within a project.
     */
    public function index(int $projectId): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $userId = session()->get('user_id');

        // Security: verify project belongs to user
        if (! $this->projectModel->belongsToUser($projectId, $userId)) {
            return redirect()->to('/spaces')->with('error', 'Proyek tidak ditemukan.');
        }

        $project  = $this->projectModel->getProjectWithSpace($projectId);
        $priority = $this->request->getGet('priority');
        $status   = $this->request->getGet('status');
        $tickets  = $this->ticketModel->getTicketsByProject($projectId, $priority, $status);
        $stats    = $this->ticketModel->getTicketStats($projectId);

        return view('tickets/index', [
            'title'            => "Tiket: {$project['name']}",
            'sidebar_tree'     => $this->spaceModel->getSidebarTree($userId),
            'project'          => $project,
            'tickets'          => $tickets,
            'stats'            => $stats,
            'active_priority'  => $priority,
            'active_status'    => $status,
            'priorities'       => TicketModel::PRIORITIES,
            'priority_colors'  => TicketModel::PRIORITY_COLORS,
            'status_colors'    => TicketModel::STATUS_COLORS,
        ]);
    }

    // ----------------------------------------------------------------
    // AJAX Endpoints
    // ----------------------------------------------------------------

    /**
     * POST /tickets — Create a new ticket.
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId    = session()->get('user_id');
        $projectId = (int) $this->request->getPost('project_id');

        if (! $this->projectModel->belongsToUser($projectId, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Proyek tidak valid.',
            ])->setStatusCode(403);
        }

        $rules = [
            'project_id'  => 'required|integer',
            'title'       => 'required|min_length[2]|max_length[200]',
            'description' => 'permit_empty',
            'priority'    => 'required|in_list[urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important]',
            'status'      => 'required|in_list[pending,ongoing,done,waiting-approval,cancelled]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $id = $this->ticketModel->insert([
            'project_id'  => $projectId,
            'title'       => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'priority'    => $this->request->getPost('priority'),
            'status'      => $this->request->getPost('status') ?: 'pending',
        ]);

        if (! $id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membuat tiket.',
            ])->setStatusCode(500);
        }

        $ticket = $this->ticketModel->find($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tiket berhasil dibuat.',
            'data'    => $ticket,
            'priority_color' => TicketModel::PRIORITY_COLORS[$ticket['priority']] ?? 'secondary',
            'status_color'   => TicketModel::STATUS_COLORS[$ticket['status']] ?? 'secondary',
        ]);
    }

    /**
     * PUT /tickets/{id} — Update a ticket.
     */
    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');
        $ticket = $this->ticketModel->find($id);

        if (! $ticket || ! $this->projectModel->belongsToUser($ticket['project_id'], $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tiket tidak ditemukan.',
            ])->setStatusCode(403);
        }

        $input = $this->request->getRawInput();
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $rules = [
            'title'       => 'required|min_length[2]|max_length[200]',
            'description' => 'permit_empty',
            'priority'    => 'required|in_list[urgent-important,important-not-urgent,urgent-not-important,not-urgent-not-important]',
            'status'      => 'required|in_list[pending,ongoing,done,waiting-approval,cancelled]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $this->ticketModel->update($id, [
            'title'       => $input['title'] ?? '',
            'description' => $input['description'] ?? '',
            'priority'    => $input['priority'] ?? 'not-urgent-not-important',
            'status'      => $input['status'] ?? 'pending',
        ]);

        $updated = $this->ticketModel->find($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tiket berhasil diperbarui.',
            'data'    => $updated,
            'priority_color' => TicketModel::PRIORITY_COLORS[$updated['priority']] ?? 'secondary',
            'status_color'   => TicketModel::STATUS_COLORS[$updated['status']] ?? 'secondary',
        ]);
    }

    /**
     * DELETE /tickets/{id} — Delete a ticket.
     */
    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');
        $ticket = $this->ticketModel->find($id);

        if (! $ticket || ! $this->projectModel->belongsToUser($ticket['project_id'], $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tiket tidak ditemukan.',
            ])->setStatusCode(403);
        }

        $this->ticketModel->delete($id);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Tiket berhasil dihapus.',
            'project_id' => $ticket['project_id'],
        ]);
    }

    /**
     * POST /tickets/{id}/status — Quick-update ticket status (AJAX).
     */
    public function updateStatus(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');
        $ticket = $this->ticketModel->find($id);

        if (! $ticket || ! $this->projectModel->belongsToUser($ticket['project_id'], $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tiket tidak ditemukan.',
            ])->setStatusCode(403);
        }

        $newStatus = $this->request->getPost('status') ?? $this->request->getRawInput()['status'] ?? '';
        $validStatuses = ['pending', 'ongoing', 'done', 'waiting-approval', 'cancelled'];

        if (! in_array($newStatus, $validStatuses)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Status tidak valid.',
            ])->setStatusCode(422);
        }

        $this->ticketModel->updateStatus($id, $newStatus);

        return $this->response->setJSON([
            'success'      => true,
            'message'      => 'Status diperbarui.',
            'status'       => $newStatus,
            'status_color' => TicketModel::STATUS_COLORS[$newStatus] ?? 'secondary',
        ]);
    }
}
