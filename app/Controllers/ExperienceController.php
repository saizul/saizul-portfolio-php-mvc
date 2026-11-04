<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\SEO;
use App\Models\Experience;

class ExperienceController extends Controller
{
    public function index(): void
    {
        SEO::init('experience');
        $model = new Experience();
        $this->render('pages.experience', [
            'work'      => $model->workOnly(),
            'education' => $model->educationOnly(),
        ], 'main');
    }
}
