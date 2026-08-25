<?php

namespace App\Controllers;

use App\Models\SpaceModel;
use App\Models\ProjectModel;

/**
 * Project Controller — CRUD for Projects within Spaces.
 */
class Project extends BaseController
{
    protected SpaceModel   $spaceModel;
    protected ProjectModel $projectModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->spaceModel   = new SpaceModel();
        $this->projectModel = new ProjectModel();
    }

    // ----------------------------------------------------------------
    // Page Views
    // ----------------------------------------------------------------

    /**
     * GET /spaces/{space_id}/projects — List projects within a space.
     */
    public function index(int $spaceId): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $userId = session()->get('user_id');

        // Security: verify space belongs to user
        if (! $this->spaceModel->belongsToUser($spaceId, $userId)) {
            return redirect()->to('/spaces')->with('error', 'Space tidak ditemukan.');
        }

        $space    = $this->spaceModel->find($spaceId);
        $projects = $this->projectModel->getProjectsBySpace($spaceId);

        return view('projects/index', [
            'title'        => "Proyek di {$space['name']}",
            'sidebar_tree' => $this->spaceModel->getSidebarTree($userId),
            'space'        => $space,
            'projects'     => $projects,
        ]);
    }

    // ----------------------------------------------------------------
    // AJAX Endpoints
    // ----------------------------------------------------------------

    /**
     * POST /projects — Create a new project.
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = session()->get('user_id');
        $spaceId  = (int) $this->request->getPost('space_id');

        // Security: verify space ownership
        if (! $this->spaceModel->belongsToUser($spaceId, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Space tidak valid.',
            ])->setStatusCode(403);
        }

        $rules = [
            'space_id'    => 'required|integer',
            'name'        => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
            'type'        => 'required|in_list[routine,seasonal]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $id = $this->projectModel->insert([
            'space_id'    => $spaceId,
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'type'        => $this->request->getPost('type'),
        ]);

        if (! $id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membuat proyek.',
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Proyek berhasil dibuat.',
            'data'    => $this->projectModel->find($id),
        ]);
    }

    /**
     * PUT /projects/{id} — Update an existing project.
     */
    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');

        if (! $this->projectModel->belongsToUser($id, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Proyek tidak ditemukan.',
            ])->setStatusCode(403);
        }

        $input = $this->request->getRawInput();
        // Also accept POST body for method spoofing
        if (empty($input)) {
            $input = $this->request->getPost();
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
            'type'        => 'required|in_list[routine,seasonal]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $this->projectModel->update($id, [
            'name'        => $input['name'] ?? '',
            'description' => $input['description'] ?? '',
            'type'        => $input['type'] ?? 'routine',
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Proyek berhasil diperbarui.',
            'data'    => $this->projectModel->find($id),
        ]);
    }

    /**
     * DELETE /projects/{id} — Delete a project.
     */
    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');

        if (! $this->projectModel->belongsToUser($id, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Proyek tidak ditemukan.',
            ])->setStatusCode(403);
        }

        $project = $this->projectModel->find($id);
        $this->projectModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Proyek berhasil dihapus.',
            'space_id' => $project['space_id'],
        ]);
    }

    // ----------------------------------------------------------------
    // API
    // ----------------------------------------------------------------

    /**
     * GET /api/spaces/{space_id}/projects — Return projects for a space as JSON.
     * Used by session create form cascading dropdown.
     */
    public function apiBySpace(int $spaceId): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');

        if (! $this->spaceModel->belongsToUser($spaceId, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'data'    => [],
            ])->setStatusCode(403);
        }

        $projects = $this->projectModel->where('space_id', $spaceId)->orderBy('name', 'ASC')->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data'    => $projects,
        ]);
    }
}
