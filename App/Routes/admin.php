<?php
use Modules\Main\Router;

$router = Router::getInstance();

$router->get('/admin/login/', \Controllers\Admin\AuthController::class, 'login');
$router->get('/admin/', \Controllers\Admin\HomeController::class, 'index');
