<?php
require_once __DIR__ . '/Modules/Main/Autoloader.php';
require_once __DIR__ . '/Modules/Main/App.php';

use Modules\Main\Router;
use Modules\Main\ViewData;

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

// Пользователи
$router->get('/api/users', \Controllers\Api\UserController::class, 'index');
$router->get('/api/users/{id}', \Controllers\Api\UserController::class, 'show');
$router->post('/api/users', \Controllers\Api\UserController::class, 'store');
$router->put('/api/users/{id}', \Controllers\Api\UserController::class, 'update');
$router->delete('/api/users/{id}', \Controllers\Api\UserController::class, 'destroy');



/* ######################## SET ROUTE ######################## */
$match = $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

if (!$match)
{
    echo '404';
    return;
}

/* ######################## PREPARE PAGE ######################## */
[$controllerClass, $action, $paramsAssoc] = $match;
$controller = new $controllerClass();
$params = array_values($paramsAssoc);

ob_start();
call_user_func_array([$controller, $action], $params);
$html = ob_get_clean();

$viewData = ViewData::getInstance();
$html = $viewData->replacePlaceholders($html);

echo $html;