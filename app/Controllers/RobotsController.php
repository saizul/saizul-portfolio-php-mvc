<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Robots;

class RobotsController extends Controller
{
    public function index(): void
    {
        Robots::serve();
    }

    public function save(): void
    {
        \App\Core\Auth::check();
        \App\Core\Auth::verifyCsrf();

        $rules = $_POST['robots_rules'] ?? '';
        (new \App\Models\Setting())->set('robots_rules', $rules);

        // Rewrite robots.txt immediately
        Robots::write();

        $this->redirect('/admin/seo?robots=saved');
    }
}
