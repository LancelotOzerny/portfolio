<?php
require_once __DIR__ . '/Modules/Main/Autoloader.php';
require_once __DIR__ . '/Modules/Main/App.php';

use Modules\Main\App;

/* ######################## CLASS REGISTER ######################## */
$loader = \Modules\Main\Autoloader::getInstance();
$loader->addPath('Modules\\', App::getInstance()->root . '/Core/Modules');
$loader->addPath('Controllers\\', App::getInstance()->root . '/App/Controllers');
$loader->addPath('Models\\', App::getInstance()->root . '/App/Models');
$loader->addPath('Develop\\', App::getInstance()->root . '/Develop');
$loader->addPath('App\\', App::getInstance()->root . '/App');
$loader->addPath('Components\\', App::getInstance()->root . '/public_html/Components');
$loader->register();

App::getInstance()->init();
App::getInstance()->start();