<?php declare(strict_types=1);
namespace App\Models;
use App\Core\{Model, Database};

class Analytics extends Model {
    protected string $table = 'analytics_events';

    public function record(array $data): void {
        $this->db()->execute(
            "INSERT INTO analytics_events (session_id,page_path,referrer,user_agent,device_type,browser,os,ip_hash,created_at)
             VALUES (?,?,?,?,?,?,?,?,NOW())",
            [
                $data['session_id'],
                $data['page_path'],
                $data['referrer']   ?? null,
                $data['user_agent'] ?? null,
                $data['device_type']?? 'desktop',
                $data['browser']    ?? null,
                $data['os']         ?? null,
                $data['ip_hash']    ?? null,
            ]
        );
    }

    public function dailyPageViews(int $days = 30): array {
        return $this->db()->fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS views
             FROM analytics_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at) ORDER BY day ASC",
            [$days]
        );
    }

    public function weeklyPageViews(int $weeks = 12): array {
        return $this->db()->fetchAll(
            "SELECT YEARWEEK(created_at,1) AS week, COUNT(*) AS views
             FROM analytics_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? WEEK)
             GROUP BY YEARWEEK(created_at,1) ORDER BY week ASC",
            [$weeks]
        );
    }

    public function topPages(int $limit = 10, int $days = 30): array {
        return $this->db()->fetchAll(
            "SELECT page_path, COUNT(*) AS views, COUNT(DISTINCT session_id) AS unique_visitors
             FROM analytics_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY page_path ORDER BY views DESC LIMIT ?",
            [$days, $limit]
        );
    }

    public function deviceBreakdown(int $days = 30): array {
        return $this->db()->fetchAll(
            "SELECT device_type, COUNT(*) AS cnt FROM analytics_events
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY device_type ORDER BY cnt DESC",
            [$days]
        );
    }

    public function topReferrers(int $limit = 10, int $days = 30): array {
        return $this->db()->fetchAll(
            "SELECT referrer, COUNT(*) AS cnt FROM analytics_events
             WHERE referrer IS NOT NULL AND referrer != ''
               AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY referrer ORDER BY cnt DESC LIMIT ?",
            [$days, $limit]
        );
    }

    public function totals(int $days = 30): array {
        return $this->db()->fetchOne(
            "SELECT COUNT(*) AS page_views, COUNT(DISTINCT session_id) AS visitors
             FROM analytics_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        ) ?? ['page_views'=>0,'visitors'=>0];
    }

    public function totalAllTime(): array {
        return $this->db()->fetchOne(
            "SELECT COUNT(*) AS page_views, COUNT(DISTINCT session_id) AS visitors FROM analytics_events"
        ) ?? ['page_views'=>0,'visitors'=>0];
    }
}
