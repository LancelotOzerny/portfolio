<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $root . '/public_html';
$_SERVER['REQUEST_URI'] = '/cron/';

require_once $root . '/Core/Modules/Main/Autoloader.php';
require_once $root . '/Core/Modules/Main/App.php';

use App\Services\Cron\CronRunner;
use Modules\Main\App;
use Modules\Main\Autoloader;

$loader = Autoloader::getInstance();
$loader->addPath('Modules\\', App::getInstance()->root . '/Core/Modules');
$loader->addPath('Controllers\\', App::getInstance()->root . '/App/Controllers');
$loader->addPath('Models\\', App::getInstance()->root . '/App/Models');
$loader->addPath('Develop\\', App::getInstance()->root . '/Develop');
$loader->addPath('App\\', App::getInstance()->root . '/App');
$loader->addPath('Components\\', App::getInstance()->root . '/public_html/Components');
$loader->register();

App::getInstance()->init();

exit((new CronRunner())->run());
