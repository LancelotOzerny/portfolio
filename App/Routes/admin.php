<?php
use Modules\Main\Router;

$router = Router::getInstance();

$router->get('/admin/login/', \Controllers\Admin\AuthController::class, 'login');
$router->get('/admin/', \Controllers\Admin\HomeController::class, 'index');
$router->get('/admin/projects/', \Controllers\Admin\ProjectsController::class, 'index');
$router->get('/admin/users/', \Controllers\Admin\UsersController::class, 'index');
$router->get('/admin/configs/', \Controllers\Admin\ConfigsController::class, 'index');
$router->post('/admin/configs/save/', \Controllers\Admin\ConfigsController::class, 'save');
$router->get('/admin/settings/', \Controllers\Admin\SettingsController::class, 'index');
$router->post('/admin/settings/repository/update/', \Controllers\Admin\SettingsController::class, 'updateRepository');
$router->post('/admin/projects/create/', \Controllers\Admin\ProjectsController::class, 'create');
$router->get('/admin/projects/{id}/', \Controllers\Admin\ProjectsController::class, 'detail');
$router->post('/admin/projects/{id}/', \Controllers\Admin\ProjectsController::class, 'updateMainInfo');
