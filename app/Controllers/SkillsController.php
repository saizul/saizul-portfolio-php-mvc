<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\SEO;
use App\Models\Skill;

class SkillsController extends Controller
{
    public function index(): void
    {
        SEO::init('skills');
        $this->render('pages.skills', [
            'skills_grouped' => (new Skill())->byCategory(),
        ], 'main');
    }
}
