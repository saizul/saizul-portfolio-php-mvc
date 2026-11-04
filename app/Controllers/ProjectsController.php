<?php declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Controller, SEO};
use App\Models\AIProject;

class ProjectsController extends Controller {
    public function index(): void {
        $model    = new AIProject();
        $projects = $model->published();
        SEO::init('ai-projects');
        SEO::setTitle('AI Projects — Saizul Amin');
        SEO::setDescription('Explore AI systems, machine learning projects, and intelligent automation built by Saizul Amin.');
        SEO::setBreadcrumbs([['Home','/'],['AI Projects',null]]);
        $this->render('pages.projects', compact('projects'), 'main');
    }

    public function single(string $slug): void {
        $model   = new AIProject();
        $project = $model->findBySlug($slug);
        if (!$project) { http_response_code(404); $this->render('errors.404',[],'main'); return; }
        SEO::init('ai-projects/' . $slug);
        SEO::setTitle(($project['meta_title'] ?: $project['title']) . ' — AI Projects');
        if ($project['meta_description']) SEO::setDescription($project['meta_description']);
        SEO::setBreadcrumbs([['Home','/'],['AI Projects','/ai-projects'],[$project['title'],null]]);
        SEO::addSoftwareAppSchema(['name'=>$project['title'],'description'=>$project['solution']??'','url'=>url('/ai-projects/'.$slug)]);
        $images = json_decode($project['images'] ?? '[]', true) ?: [];
        $this->render('pages.ai-project-single', compact('project','images'), 'main');
    }
}
