<?php declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\{Controller, Auth};
use App\Models\Analytics;

class AnalyticsController extends Controller {
    private Analytics $model;
    public function __construct() { Auth::requireAdmin(); $this->model = new Analytics(); }

    public function index(): void {
        $days       = (int)($_GET['days'] ?? 30);
        $daily      = $this->model->dailyPageViews($days);
        $topPages   = $this->model->topPages(10, $days);
        $devices    = $this->model->deviceBreakdown($days);
        $referrers  = $this->model->topReferrers(10, $days);
        $totals     = $this->model->totals($days);
        $allTime    = $this->model->totalAllTime();
        $this->render('admin.analytics.index', compact('daily','topPages','devices','referrers','totals','allTime','days'), 'admin');
    }

    /** AJAX data endpoint for charts */
    public function data(): void {
        $days = (int)($_GET['days'] ?? 30);
        $mode = $_GET['mode'] ?? 'daily';
        $data = $mode === 'weekly'
            ? $this->model->weeklyPageViews((int)ceil($days/7))
            : $this->model->dailyPageViews($days);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /** Called via lightweight JS beacon on every public page load */
    public function record(): void {
        // Only accept POST from same origin
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $host   = $_SERVER['HTTP_HOST']   ?? '';
        if ($origin && parse_url($origin, PHP_URL_HOST) !== $host) {
            http_response_code(403); exit;
        }

        session_start();
        $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $session = session_id();
        $ip      = hash('sha256', ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '') . 'saizul_salt');

        // Simple device detection
        $device = 'desktop';
        if (preg_match('/Mobile|Android|iPhone/i', $ua)) $device = 'mobile';
        elseif (preg_match('/iPad|Tablet/i', $ua)) $device = 'tablet';

        // Simple browser detection
        $browser = 'Other';
        foreach (['Chrome'=>'Chrome','Firefox'=>'Firefox','Safari'=>'Safari','Edge'=>'Edg','Opera'=>'OPR'] as $name => $token) {
            if (str_contains($ua, $token)) { $browser = $name; break; }
        }

        $os = 'Other';
        foreach (['Windows','Macintosh','Linux','Android','iOS'=>'iPhone'] as $name => $token) {
            if (str_contains($ua, $token)) { $os = $name; break; }
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $path = $data['path']     ?? ($_SERVER['HTTP_REFERER'] ?? '/');
        $ref  = $data['referrer'] ?? '';

        (new \App\Models\Analytics())->record([
            'session_id'  => $session,
            'page_path'   => substr($path, 0, 512),
            'referrer'    => $ref ? substr($ref, 0, 512) : null,
            'user_agent'  => substr($ua, 0, 512),
            'device_type' => $device,
            'browser'     => $browser,
            'os'          => $os,
            'ip_hash'     => $ip,
        ]);

        header('Content-Type: application/json');
        echo '{"ok":true}';
        exit;
    }
}
