<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\SEOMeta;

class SchemaController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->render('admin.schema.index', [], 'admin');
    }

    public function save(string $slug): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $json = $this->post('schema_json', '');

        // Validate JSON
        if ($json && !json_decode($json)) {
            $this->json(['success' => false, 'error' => 'Invalid JSON'], 422);
        }

        (new SEOMeta())->upsert($slug, ['schema_json' => $json]);
        $this->json(['success' => true]);
    }
}
