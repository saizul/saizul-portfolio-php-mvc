<?php

declare(strict_types=1);

use App\Core\Router;

/**
 * Route definitions.
 * $router is injected by public/index.php before this file is required.
 *
 * @var Router $router
 */

// ------------------------------------------------------------------
// Public routes
// ------------------------------------------------------------------

$router->get('/',              'HomeController@index');
$router->get('/about',         'AboutController@index');
$router->get('/ai-projects',   'ProjectsController@index');
$router->get('/experience',    'ExperienceController@index');
$router->get('/skills',        'SkillsController@index');
$router->get('/portfolio',     'PortfolioController@index');
$router->get('/contact',       'ContactController@index');
$router->post('/contact',      'ContactController@send');
$router->get('/blog',          'BlogController@index');
$router->get('/blog/{slug}',   'BlogController@single');
$router->get('/ai-projects/{slug}', 'ProjectsController@single');
$router->get('/blog/category/{slug}', 'BlogController@category');
$router->get('/blog/tag/{slug}',      'BlogController@tag');
$router->get('/search',        'SearchController@index');
$router->get('/search/ajax',   'SearchController@ajax');

// Sitemap & Robots (public)
$router->get('/sitemap.xml',              'SitemapController@index');
$router->get('/robots.txt',               'RobotsController@index');

// ------------------------------------------------------------------
// Admin routes
// ------------------------------------------------------------------

$router->get('/admin',                    'Admin\DashboardController@index');

// Auth
$router->get('/admin/login',                  'Admin\AuthController@loginForm');
$router->post('/admin/login',                 'Admin\AuthController@login');
$router->get('/admin/logout',                 'Admin\AuthController@logout');
$router->get('/admin/forgot',                 'Admin\AuthController@forgotForm');
$router->post('/admin/forgot',                'Admin\AuthController@forgot');
$router->get('/admin/reset/{token}',          'Admin\AuthController@resetForm');
$router->post('/admin/reset/{token}',         'Admin\AuthController@reset');

// Blog / Posts
$router->get('/admin/posts',              'Admin\PostController@index');
$router->get('/admin/posts/create',       'Admin\PostController@create');
$router->post('/admin/posts/store',       'Admin\PostController@store');
$router->get('/admin/posts/{id}/edit',    'Admin\PostController@edit');
$router->post('/admin/posts/{id}/update', 'Admin\PostController@update');
$router->post('/admin/posts/{id}/delete', 'Admin\PostController@delete');

// Post categories & tags
$router->get('/admin/post-categories',                  'Admin\PostCategoryController@index');
$router->post('/admin/post-categories/store',           'Admin\PostCategoryController@store');
$router->post('/admin/post-categories/{id}/delete',     'Admin\PostCategoryController@delete');
$router->get('/admin/post-tags',                        'Admin\PostTagController@index');
$router->post('/admin/post-tags/store',                 'Admin\PostTagController@store');
$router->post('/admin/post-tags/{id}/delete',           'Admin\PostTagController@delete');

// Portfolio / Projects
$router->get('/admin/portfolio',               'Admin\PortfolioController@index');
$router->get('/admin/portfolio/create',        'Admin\PortfolioController@create');
$router->post('/admin/portfolio/store',        'Admin\PortfolioController@store');
$router->get('/admin/portfolio/{id}/edit',     'Admin\PortfolioController@edit');
$router->post('/admin/portfolio/{id}/update',  'Admin\PortfolioController@update');
$router->post('/admin/portfolio/{id}/delete',  'Admin\PortfolioController@delete');
$router->post('/admin/portfolio/reorder',      'Admin\PortfolioController@reorder');
$router->get('/admin/portfolio-categories',              'Admin\PortfolioCategoryController@index');
$router->post('/admin/portfolio-categories/store',       'Admin\PortfolioCategoryController@store');
$router->post('/admin/portfolio-categories/{id}/delete', 'Admin\PortfolioCategoryController@delete');

// AI Projects
$router->get('/admin/ai-projects',               'Admin\AIProjectController@index');
$router->get('/admin/ai-projects/create',        'Admin\AIProjectController@create');
$router->post('/admin/ai-projects/store',        'Admin\AIProjectController@store');
$router->get('/admin/ai-projects/{id}/edit',     'Admin\AIProjectController@edit');
$router->post('/admin/ai-projects/{id}/update',  'Admin\AIProjectController@update');
$router->post('/admin/ai-projects/{id}/delete',  'Admin\AIProjectController@delete');

// Skills
$router->get('/admin/skills',                'Admin\SkillController@index');
$router->post('/admin/skills/store',         'Admin\SkillController@store');
$router->post('/admin/skills/{id}/update',   'Admin\SkillController@update');
$router->post('/admin/skills/{id}/delete',   'Admin\SkillController@delete');

// Experience
$router->get('/admin/experience',             'Admin\ExperienceController@index');
$router->post('/admin/experience/store',      'Admin\ExperienceController@store');
$router->post('/admin/experience/{id}/update','Admin\ExperienceController@update');
$router->post('/admin/experience/{id}/delete','Admin\ExperienceController@delete');

// Media
$router->get('/admin/media',                    'Admin\MediaController@index');
$router->post('/admin/media/upload',            'Admin\MediaController@upload');
$router->post('/admin/media/bulk-upload',       'Admin\MediaController@bulkUpload');
$router->post('/admin/media/{id}/update',       'Admin\MediaController@update');
$router->post('/admin/media/{id}/delete',       'Admin\MediaController@delete');
$router->post('/admin/media/bulk-delete',       'Admin\MediaController@bulkDelete');
$router->get('/admin/media/folder/{folder}',    'Admin\MediaController@folder');

// Page Builder
$router->get('/admin/pages',                     'Admin\PageBuilderController@index');
$router->post('/admin/pages/create',             'Admin\PageBuilderController@create');
$router->get('/admin/pages/{slug}/edit',         'Admin\PageBuilderController@edit');
$router->post('/admin/pages/{slug}/save',        'Admin\PageBuilderController@save');
$router->post('/admin/pages/{slug}/delete',      'Admin\PageBuilderController@delete');

// SEO
$router->get('/admin/seo',                'Admin\SEOController@index');
$router->get('/admin/seo/{slug}',         'Admin\SEOController@edit');
$router->post('/admin/seo/{slug}/save',   'Admin\SEOController@save');
$router->post('/admin/sitemap/generate',  'Admin\SEOController@generateSitemap');
$router->post('/admin/robots/save',       'Admin\SEOController@saveRobots');
$router->post('/admin/sitemap/regenerate','SitemapController@regenerate');

// Schema
$router->get('/admin/schema',              'Admin\SchemaController@index');
$router->post('/admin/schema/{slug}/save', 'Admin\SchemaController@save');

// Settings
$router->get('/admin/settings',           'Admin\SettingsController@index');
$router->post('/admin/settings/save',     'Admin\SettingsController@save');

// Theme
$router->get('/admin/theme',              'Admin\ThemeController@index');
$router->post('/admin/theme/save',        'Admin\ThemeController@save');
$router->get('/admin/theme/preview',      'Admin\ThemeController@preview');
$router->get('/admin/theme/css',          'Admin\ThemeController@css');

// Analytics
$router->get('/admin/analytics',          'Admin\AnalyticsController@index');
$router->get('/admin/analytics/data',     'Admin\AnalyticsController@data');
$router->post('/admin/analytics/record',  'Admin\AnalyticsController@record');

// Dynamic Content Engine
$router->get('/admin/menu',               'Admin\MenuController@index');
$router->post('/admin/menu/store',        'Admin\MenuController@store');
$router->post('/admin/menu/{id}/update',  'Admin\MenuController@update');
$router->post('/admin/menu/{id}/delete',  'Admin\MenuController@delete');
$router->post('/admin/menu/reorder',      'Admin\MenuController@reorder');

$router->get('/admin/homepage',                  'Admin\HomepageSectionsController@index');
$router->get('/admin/homepage/{key}/edit',       'Admin\HomepageSectionsController@edit');
$router->post('/admin/homepage/{key}/save',      'Admin\HomepageSectionsController@save');
$router->post('/admin/homepage/toggle',          'Admin\HomepageSectionsController@toggle');
$router->post('/admin/homepage/reorder',         'Admin\HomepageSectionsController@reorder');

$router->get('/admin/footer',             'Admin\FooterController@index');
$router->post('/admin/footer/save',       'Admin\FooterController@save');
$router->post('/admin/footer/{key}/delete','Admin\FooterController@delete');

// Dynamic CMS pages — catch-all (MUST be last)
$router->get('/{slug}', 'PageController@show');
