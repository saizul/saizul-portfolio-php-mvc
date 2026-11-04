<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Post;
use App\Models\Project;
use App\Models\Media;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $db = Database::getInstance();

        $stats = [
            'posts'        => (new Post())->count(),
            'projects'     => (new Project())->count(),
            'media'        => (new Media())->count(),
            'unread_msgs'  => (int) $db->query("SELECT COUNT(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn(),
        ];

        $this->render('admin.dashboard', [
            'stats'        => $stats,
            'recent_posts' => (new Post())->paginate(1, 5, '', [], 'created_at', 'DESC')['data'],
        ], 'admin');
    }
}
