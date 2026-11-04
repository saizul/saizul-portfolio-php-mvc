<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Experience;

class ExperienceController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->render('admin.experience.index', [
            'rows' => (new Experience())->findAll(),
        ], 'admin');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        (new Experience())->create([
            'company'     => sanitizeString($this->post('company', '')),
            'role'        => sanitizeString($this->post('role', '')),
            'description' => sanitizeString($this->post('description', '')),
            'start_date'  => $this->post('start_date', ''),
            'end_date'    => $this->post('is_current') ? null : ($this->post('end_date') ?: null),
            'is_current'  => $this->post('is_current') ? 1 : 0,
            'type'        => in_array($this->post('type'), ['work','education']) ? $this->post('type') : 'work',
            'order'       => (new Experience())->count() + 1,
        ]);

        $this->flash('success', 'Experience added.');
        $this->redirect(url('/admin/experience'));
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        (new Experience())->update((int)$id, [
            'company'     => sanitizeString($this->post('company', '')),
            'role'        => sanitizeString($this->post('role', '')),
            'description' => sanitizeString($this->post('description', '')),
            'start_date'  => $this->post('start_date', ''),
            'end_date'    => $this->post('is_current') ? null : ($this->post('end_date') ?: null),
            'is_current'  => $this->post('is_current') ? 1 : 0,
            'type'        => in_array($this->post('type'), ['work','education']) ? $this->post('type') : 'work',
        ]);

        $this->flash('success', 'Experience updated.');
        $this->redirect(url('/admin/experience'));
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        (new Experience())->delete((int)$id);
        $this->flash('success', 'Experience deleted.');
        $this->redirect(url('/admin/experience'));
    }

    public function reorder(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $order = json_decode($this->post('order', '[]'), true) ?? [];
        $model = new Experience();
        foreach ($order as $i => $id) {
            $model->update((int)$id, ['order' => $i + 1]);
        }
        $this->json(['success' => true]);
    }
}
