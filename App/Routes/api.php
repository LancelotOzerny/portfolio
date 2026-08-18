<?php
use Modules\Main\Router;
$router = Router::getInstance();

$router->get('/api/users/', \Controllers\Api\UserController::class, 'getAll');
$router->post('/api/feedback/send/', \Controllers\Api\FeedbackController::class, 'send');
$router->post('/api/include-area/save/', \Controllers\Api\IncludeAreaController::class, 'save');
$router->post('/api/component/settings/save/', \Controllers\Api\ComponentController::class, 'saveSettings');
$router->get('/api/images/', \Controllers\Api\ImagesController::class, 'list');
$router->get('/api/gallery/', \Controllers\Api\GalleryController::class, 'list');
$router->get('/api/blog/', \Controllers\Api\BlogController::class, 'list');
$router->get('/api/blog/{topic}/', \Controllers\Api\BlogController::class, 'topic');
$router->get('/api/blog/{topic}/{article}/', \Controllers\Api\BlogController::class, 'article');
$router->post('/api/auth/login/', \Controllers\AuthController::class, 'login');
