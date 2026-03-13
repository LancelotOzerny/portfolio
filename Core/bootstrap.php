<?php
require_once __DIR__ . '/Modules/Main/Autoloader.php';
require_once __DIR__ . '/Modules/Main/App.php';

use Modules\Main\Router;

$app = Modules\Main\App::getInstance();



/* ######################## CLASS REGISTER ######################## */
$loader = \Modules\Main\Autoloader::getInstance();
$loader->addPath('Modules\\', $app->root . '/Core/Modules');
$loader->addPath('Controllers\\', $app->root . '/App/Controllers');
$loader->addPath('Develop\\', $app->root . '/Develop');
$loader->addPath('App\\', $app->root . '/App');
$loader->register();



/* ######################## ROUTES ######################## */
$router = new Router();
$router->get('/', \Controllers\Public\HomeController::class,  'index');



/* ######################## SET ROUTE ######################## */
$match = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

if (!$match)
{
    echo '404';
    return;
}

[$controllerClass, $action, $paramsAssoc] = $match;
$controller = new $controllerClass();
$params = array_values($paramsAssoc);
call_user_func_array([$controller, $action], $params);