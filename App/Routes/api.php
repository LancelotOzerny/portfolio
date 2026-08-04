<?php
use Modules\Main\Router;
$router = Router::getInstance();

$router->get('/api/users/', \Controllers\Api\UserController::class, 'getAll');
$router->post('/api/feedback/send/', \Controllers\Api\FeedbackController::class, 'send');
$router->post('/api/include-area/save/', \Controllers\Api\IncludeAreaController::class, 'save');
$router->post('/api/component/settings/save/', \Controllers\Api\ComponentController::class, 'saveSettings');
$router->get('/api/images/', \Controllers\Api\ImagesController::class, 'list');
$router->post('/api/auth/login/', \Controllers\AuthController::class, 'login');
