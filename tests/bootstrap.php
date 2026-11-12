<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap: mirrors the constants public/index.php defines, then
 * loads Composer's autoloader (App\ classes + global helper functions).
 */

define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

date_default_timezone_set('UTC');

require BASE_PATH . '/vendor/autoload.php';
