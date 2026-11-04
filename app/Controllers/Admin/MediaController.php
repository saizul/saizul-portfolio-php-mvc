<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth, Upload};
use App\Models\Media;

class MediaController extends Controller {
    private Media $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new Media(); }

    public function index(): void {
        $folder = $_GET['folder'] ?? 'all';
        $items  = $folder === 'all'
            ? $this->model->all()
            : $this->model->byFolder($folder);
        $this->render('admin.media.index', compact('items','folder'), 'admin');
    }

    public function folder(string $folder): void {
        $items = $this->model->byFolder($folder);
        $this->render('admin.media.index', compact('items','folder'), 'admin');
    }

    /** Single or AJAX single upload */
    public function upload(): void {
        Auth::verifyCsrf();
        $folder = $_POST['folder'] ?? 'general';
        try {
            $result = Upload::handle('file', $folder, true);
            $id = $this->model->create([
                'filename'  => $result['filename'],
                'filepath'  => $result['path'],
                'webp_path' => $result['webp_path'],
                'mime_type' => $result['mime'],
                'alt_text'  => trim($_POST['alt_text'] ?? ''),
                'title'     => trim($_POST['title']    ?? ''),
                'folder'    => $result['folder'],
                'size'      => $result['size'],
                'width'     => $result['width'],
                'height'    => $result['height'],
            ]);
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['ok'=>true,'id'=>$id,'path'=>$result['path'],'webp'=>$result['webp_path']]);
                exit;
            }
        } catch (\Throwable $e) {
            if ($this->isAjax()) {
                header('Content-Type: application/json'); http_response_code(422);
                echo json_encode(['ok'=>false,'error'=>$e->getMessage()]); exit;
            }
            $this->redirect('/admin/media', $e->getMessage(), 'error');
            return;
        }
        $this->redirect('/admin/media', 'File uploaded.');
    }

    /** Bulk upload handler */
    public function bulkUpload(): void {
        Auth::verifyCsrf();
        $folder  = $_POST['folder'] ?? 'general';
        $results = Upload::handleMultiple('files', $folder, true);
        foreach ($results as $r) {
            $this->model->create([
                'filename'  => $r['filename'],
                'filepath'  => $r['path'],
                'webp_path' => $r['webp_path'],
                'mime_type' => $r['mime'],
                'alt_text'  => '',
                'folder'    => $r['folder'],
                'size'      => $r['size'],
                'width'     => $r['width'],
                'height'    => $r['height'],
            ]);
        }
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>true,'count'=>count($results)]); exit;
        }
        $this->redirect('/admin/media', count($results) . ' files uploaded.');
    }

    public function update(int $id): void {
        Auth::verifyCsrf();
        $this->model->update($id, [
            'alt_text'    => trim($_POST['alt_text']    ?? ''),
            'title'       => trim($_POST['title']       ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ]);
        if ($this->isAjax()) {
            header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
        }
        $this->redirect('/admin/media', 'Media updated.');
    }

    public function delete(int $id): void {
        Auth::verifyCsrf();
        $item = $this->model->find($id);
        if ($item) {
            $this->deleteFile($item['filepath']);
            if ($item['webp_path'] ?? '') $this->deleteFile($item['webp_path']);
            $this->model->delete($id);
        }
        if ($this->isAjax()) {
            header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
        }
        $this->redirect('/admin/media', 'File deleted.');
    }

    public function bulkDelete(): void {
        Auth::verifyCsrf();
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        foreach ((array)$ids as $id) {
            $item = $this->model->find((int)$id);
            if (!$item) continue;
            $this->deleteFile($item['filepath']);
            if ($item['webp_path'] ?? '') $this->deleteFile($item['webp_path']);
            $this->model->delete((int)$id);
        }
        if ($this->isAjax()) {
            header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
        }
        $this->redirect('/admin/media', 'Files deleted.');
    }

    private function deleteFile(string $webPath): void {
        $abs = BASE_PATH . '/public' . $webPath;
        if (file_exists($abs)) @unlink($abs);
    }

    private function isAjax(): bool { return !empty($_SERVER['HTTP_X_REQUESTED_WITH']); }
}
