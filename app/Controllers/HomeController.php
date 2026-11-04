<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\SEO;
use App\Core\Cache;
use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;

class HomeController extends Controller
{
    public function index(): void
    {
        SEO::init('home');

        // Cache homepage data for 1 hour — invalidated when content is published
        $data = Cache::remember('homepage_data', 3600, function () {
            $postModel    = new Post();
            $projectModel = new Project();
            $skillModel   = new Skill();
            return [
                'latest_posts'      => $postModel->published(3),
                'featured_projects' => $projectModel->featured(3),
                'skills_grouped'    => $skillModel->byCategory(),
            ];
        });

        $this->render('pages.home', $data, 'main');
    }
}
