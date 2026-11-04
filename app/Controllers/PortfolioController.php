<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\SEO;
use App\Models\Project;

class PortfolioController extends Controller
{
    public function index(): void
    {
        SEO::init('portfolio');
        $this->render('pages.portfolio', [
            'projects' => (new Project())->byCategory('portfolio'),
        ], 'main');
    }
}
