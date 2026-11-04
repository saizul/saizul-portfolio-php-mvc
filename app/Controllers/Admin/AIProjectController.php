<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth};
use App\Models\AIProject;

class AIProjectController extends Controller {
    private AIProject $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new AIProject(); }

    public function index(): void {
        $projects = $this->model->all();
        $this->render('admin.ai-projects.index', compact('projects'), 'admin');
    }

    public function create(): void {
        $this->render('admin.ai-projects.form', ['project'=>null,'action'=>'/admin/ai-projects/store'], 'admin');
    }

    public function store(): void {
        Auth::verifyCsrf();
        $data = $this->sanitize();
        if (!$data['title']) { $this->redirect('/admin/ai-projects/create','Title required.','error'); return; }
        $data['slug']   = $this->model->generateSlug($data['title']);
        $data['images'] = json_encode($this->collectImages());
        $this->model->create($data);
        $this->redirect('/admin/ai-projects','AI Project created.');
    }

    public function edit(int $id): void {
        $project = $this->model->find($id);
        if (!$project) { $this->redirect('/admin/ai-projects','Not found.','error'); return; }
        $this->render('admin.ai-projects.form', ['project'=>$project,'action'=>"/admin/ai-projects/{$id}/update"], 'admin');
    }

    public function update(int $id): void {
        Auth::verifyCsrf();
        $data = $this->sanitize();
        $data['images'] = json_encode($this->collectImages());
        $this->model->update($id, $data);
        $this->redirect('/admin/ai-projects','AI Project updated.');
    }

    public function delete(int $id): void {
        Auth::verifyCsrf();
        $this->model->delete($id);
        $this->redirect('/admin/ai-projects','Project deleted.');
    }

    private function sanitize(): array {
        return [
            'title'              => trim($_POST['title']               ?? ''),
            'problem'            => trim($_POST['problem']             ?? ''),
            'solution'           => trim($_POST['solution']            ?? ''),
            'workflow_diagram'   => trim($_POST['workflow_diagram']    ?? ''),
            'tech_stack'         => trim($_POST['tech_stack']          ?? ''),
            'architecture_notes' => trim($_POST['architecture_notes']  ?? ''),
            'results'            => trim($_POST['results']             ?? ''),
            'video_url'          => trim($_POST['video_url']           ?? ''),
            'status'             => $_POST['status'] ?? 'published',
            'order_col'          => (int)($_POST['order_col'] ?? 0),
            'meta_title'         => trim($_POST['meta_title']          ?? ''),
            'meta_description'   => trim($_POST['meta_description']    ?? ''),
        ];
    }

    private function collectImages(): array {
        $imgs = [];
        foreach (($_POST['image_paths'] ?? []) as $p) {
            if ($p = trim($p)) $imgs[] = $p;
        }
        return $imgs;
    }
}
