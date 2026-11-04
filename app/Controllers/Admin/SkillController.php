<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Skill;

class SkillController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->render('admin.skills.index', [
            'rows' => (new Skill())->findAll(),
        ], 'admin');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        (new Skill())->create([
            'name'        => sanitizeString($this->post('name', '')),
            'category'    => sanitizeString($this->post('category', 'General')),
            'proficiency' => (int)$this->post('proficiency', 80),
            'icon'        => sanitizeString($this->post('icon', '')),
            'order'       => (new Skill())->count() + 1,
        ]);

        $this->flash('success', 'Skill added.');
        $this->redirect(url('/admin/skills'));
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        (new Skill())->update((int)$id, [
            'name'        => sanitizeString($this->post('name', '')),
            'category'    => sanitizeString($this->post('category', '')),
            'proficiency' => (int)$this->post('proficiency', 80),
        ]);

        $this->flash('success', 'Skill updated.');
        $this->redirect(url('/admin/skills'));
    }

    public function delete(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        (new Skill())->delete((int)$id);
        $this->flash('success', 'Skill deleted.');
        $this->redirect(url('/admin/skills'));
    }

    public function reorder(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $order = json_decode($this->post('order', '[]'), true) ?? [];
        $model = new Skill();
        foreach ($order as $i => $id) {
            $model->update((int)$id, ['order' => $i + 1]);
        }
        $this->json(['success' => true]);
    }
}
