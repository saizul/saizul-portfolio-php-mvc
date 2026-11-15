<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Upload, UploadPolicy};
use App\Models\Media;

class MediaController extends Controller {
    private Media $model;
    public function __construct() { $this->requireAdmin(); $this->model = new Media(); }

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
        $this->verifyCsrf();
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
            $this->flash('error', $e->getMessage());
            $this->redirect(url('/admin/media'));
        }
        $this->flash('success', 'File uploaded.');
        $this->redirect(url('/admin/media'));
    }

    /** Bulk upload handler */
    public function bulkUpload(): void {
        $this->verifyCsrf();
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
        $rejected = Upload::lastRejected();
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>true,'count'=>count($results),'rejected'=>$rejected]); exit;
        }
        $message = count($results) . ' files uploaded.';
        if ($rejected) {
            $message .= ' ' . count($rejected) . ' skipped: ' . implode('; ', array_map(
                static fn(string $name, string $why): string => "{$name} ({$why})",
                array_keys($rejected),
                $rejected
            ));
        }
        $this->flash($rejected ? 'error' : 'success', $message);
        $this->redirect(url('/admin/media'));
    }

    public function update(string $id): void {
        $id = (int) $id;
        $this->verifyCsrf();
        $this->model->update($id, [
            'alt_text'    => trim($_POST['alt_text']    ?? ''),
            'title'       => trim($_POST['title']       ?? ''),
            'description' => trim($_POST['description'] ?? ''),
        ]);
        if ($this->isAjax()) {
            header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
        }
        $this->flash('success', 'Media updated.');
        $this->redirect(url('/admin/media'));
    }

    public function delete(string $id): void {
        $id = (int) $id;
        $this->verifyCsrf();
        $item = $this->model->find($id);
        if ($item) {
            $this->deleteFile($item['filepath']);
            if ($item['webp_path'] ?? '') $this->deleteFile($item['webp_path']);
            $this->model->delete($id);
        }
        if ($this->isAjax()) {
            header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
        }
        $this->flash('success', 'File deleted.');
        $this->redirect(url('/admin/media'));
    }

    public function bulkDelete(): void {
        $this->verifyCsrf();
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
        $this->flash('success', 'Files deleted.');
        $this->redirect(url('/admin/media'));
    }

    private function deleteFile(string $webPath): void {
        // Only ever delete inside public/uploads/, whatever the stored path says.
        $abs = UploadPolicy::resolveStoredPath(BASE_PATH . '/public', $webPath);
        if ($abs !== null && is_file($abs)) @unlink($abs);
    }

    private function isAjax(): bool { return !empty($_SERVER['HTTP_X_REQUESTED_WITH']); }
}
