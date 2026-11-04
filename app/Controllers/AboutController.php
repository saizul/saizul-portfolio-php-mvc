<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\SEO;
use App\Models\Experience;
use App\Models\Page;

class AboutController extends Controller
{
    public function index(): void
    {
        SEO::init('about');
        $this->render('pages.about', [
            'experience' => (new Experience())->ordered(),
            'blocks'     => (new Page())->getBlocks('about'),
        ], 'main');
    }
}
