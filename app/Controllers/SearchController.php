<?php declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Controller, SEO, Database};

class SearchController extends Controller {
    public function index(): void {
        $q       = trim($_GET['q'] ?? '');
        $results = $q ? $this->search($q) : [];
        SEO::init('search');
        SEO::setTitle("Search: {$q} — Saizul Amin");
        SEO::setRobots('noindex,follow');
        $this->render('pages.search', compact('q','results'), 'main');
    }

    /** AJAX live-search endpoint — returns JSON */
    public function ajax(): void {
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) { echo json_encode([]); exit; }
        $results = array_slice($this->search($q, 8), 0, 8);
        echo json_encode($results);
        exit;
    }

    private function search(string $q, int $limit = 30): array {
        $like = '%' . $q . '%';
        $db   = Database::getInstance();
        $results = [];

        // Posts
        $posts = $db->fetchAll(
            "SELECT id,'post' AS type,title,excerpt AS snippet,slug,featured_image AS image,published_at AS date
             FROM posts WHERE status='published' AND (title LIKE ? OR excerpt LIKE ? OR body LIKE ?)
             ORDER BY published_at DESC LIMIT ?",
            [$like,$like,$like,$limit]
        );
        foreach ($posts as $p) {
            $p['url']    = url('/blog/' . $p['slug']);
            $p['section']= 'Blog';
            $results[]   = $p;
        }

        // Projects / Portfolio
        $projects = $db->fetchAll(
            "SELECT id,'project' AS type,title,description AS snippet,slug,thumbnail AS image,created_at AS date
             FROM projects WHERE status='published' AND (title LIKE ? OR description LIKE ? OR tech_stack LIKE ?)
             ORDER BY order_col ASC LIMIT ?",
            [$like,$like,$like,$limit]
        );
        foreach ($projects as $p) {
            $p['url']    = url('/portfolio');
            $p['section']= 'Portfolio';
            $results[]   = $p;
        }

        // AI Projects
        $aiProjects = $db->fetchAll(
            "SELECT id,'ai_project' AS type,title,solution AS snippet,slug,NULL AS image,created_at AS date
             FROM ai_projects WHERE status='published' AND (title LIKE ? OR problem LIKE ? OR solution LIKE ? OR tech_stack LIKE ?)
             ORDER BY order_col ASC LIMIT ?",
            [$like,$like,$like,$like,$limit]
        );
        foreach ($aiProjects as $p) {
            $p['url']    = url('/ai-projects/' . $p['slug']);
            $p['section']= 'AI Projects';
            $results[]   = $p;
        }

        // Pages
        $pages = $db->fetchAll(
            "SELECT id,'page' AS type,title,NULL AS snippet,slug,NULL AS image,updated_at AS date
             FROM pages WHERE title LIKE ? OR slug LIKE ?
             ORDER BY updated_at DESC LIMIT ?",
            [$like,$like,$limit]
        );
        foreach ($pages as $p) {
            $p['url']    = url('/' . $p['slug']);
            $p['section']= 'Pages';
            $results[]   = $p;
        }

        // Score by title match first
        usort($results, function($a, $b) use ($q) {
            $aTitle = stripos($a['title'], $q) !== false ? 0 : 1;
            $bTitle = stripos($b['title'], $q) !== false ? 0 : 1;
            return $aTitle - $bTitle;
        });

        return $results;
    }
}
