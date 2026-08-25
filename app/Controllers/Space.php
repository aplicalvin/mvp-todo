<?php

namespace App\Controllers;

use App\Models\SpaceModel;

/**
 * Space Controller — CRUD for Spaces.
 * AJAX responses return JSON; page loads return views.
 */
class Space extends BaseController
{
    protected SpaceModel $spaceModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->spaceModel = new SpaceModel();
    }

    // ----------------------------------------------------------------
    // Page Views
    // ----------------------------------------------------------------

    /**
     * List all spaces for the current user.
     */
    public function index(): string
    {
        $userId = session()->get('user_id');

        return view('spaces/index', [
            'title'        => 'Spaces',
            'sidebar_tree' => $this->spaceModel->getSidebarTree($userId),
            'spaces'       => $this->spaceModel->getSpacesWithProjectCount($userId),
        ]);
    }

    // ----------------------------------------------------------------
    // AJAX Endpoints
    // ----------------------------------------------------------------

    /**
     * POST /spaces — Create a new space.
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');

        $rules = [
            'name'        => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $id = $this->spaceModel->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'user_id'     => $userId,
        ]);

        if (! $id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal membuat space.',
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Space berhasil dibuat.',
            'data'    => $this->spaceModel->find($id),
        ]);
    }

    /**
     * PUT /spaces/{id} — Update an existing space.
     */
    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');

        // Ownership check
        if (! $this->spaceModel->belongsToUser($id, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Space tidak ditemukan.',
            ])->setStatusCode(403);
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $this->spaceModel->update($id, [
            'name'        => $this->request->getPost('name') ?? $this->request->getRawInput()['name'] ?? '',
            'description' => $this->request->getPost('description') ?? $this->request->getRawInput()['description'] ?? '',
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Space berhasil diperbarui.',
            'data'    => $this->spaceModel->find($id),
        ]);
    }

    /**
     * DELETE /spaces/{id} — Delete a space.
     */
    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');

        if (! $this->spaceModel->belongsToUser($id, $userId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Space tidak ditemukan.',
            ])->setStatusCode(403);
        }

        $this->spaceModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Space berhasil dihapus.',
        ]);
    }

    // ----------------------------------------------------------------
    // API
    // ----------------------------------------------------------------

    /**
     * GET /api/spaces-tree — Return sidebar tree as JSON.
     */
    public function apiTree(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = session()->get('user_id');

        return $this->response->setJSON([
            'success' => true,
            'data'    => $this->spaceModel->getSidebarTree($userId),
        ]);
    }
}
