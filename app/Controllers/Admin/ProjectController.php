<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->render('admin.project.index', [
            'rows' => (new Project())->findAll(),
        ], 'admin');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->render('admin.project.form', ['row' => null], 'admin');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        // TODO: implement field validation and create
        $this->flash('success', 'Project created.');
        $this->redirect(url('/admin/project'));
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $row = (new Project())->find((int)$id);
        if (!$row) $this->abort(404);
        $this->render('admin.project.form', ['row' => $row], 'admin');
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        // TODO: implement field validation and update
        $this->flash('success', 'Project updated.');
        $this->redirect(url('/admin/project'));
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        (new Project())->delete((int)$id);
        $this->flash('success', 'Project deleted.');
        $this->redirect(url('/admin/project'));
    }

    public function reorder(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $order = json_decode($this->post('order', '[]'), true) ?? [];
        $model = new Project();
        foreach ($order as $i => $id) {
            $model->update((int)$id, ['order' => $i + 1]);
        }
        $this->json(['success' => true]);
    }
}
