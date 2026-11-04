<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Robots;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $setting = new Setting();
        $this->render('admin.settings.index', [
            'general' => $setting->groupAsMap('general'),
            'social'  => $setting->groupAsMap('social'),
            'seo'     => $setting->groupAsMap('seo'),
        ], 'admin');
    }

    public function save(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $setting = new Setting();
        $fields  = [
            'site_name'               => 'general',
            'site_tagline'            => 'general',
            'contact_email'           => 'general',
            'seo_default_title'       => 'seo',
            'seo_default_description' => 'seo',
            'seo_og_image'            => 'seo',
            'social_github'           => 'social',
            'social_linkedin'         => 'social',
            'social_twitter'          => 'social',
        ];

        foreach ($fields as $key => $group) {
            $value = sanitizeString($this->post($key, ''));
            $setting->set($key, $value);
        }

        // Robots
        if ($this->post('robots_rules') !== null) {
            $setting->set('robots_rules', $this->post('robots_rules', ''));
            Robots::write();
        }

        $this->flash('success', 'Settings saved.');
        $this->redirect(url('/admin/settings'));
    }
}
