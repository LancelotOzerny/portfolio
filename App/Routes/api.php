<?php
use Modules\Main\Router;
$router = Router::getInstance();

$router->get('/api/users/', \Controllers\Api\UserController::class, 'getAll');
$router->post('/api/feedback/send/', \Controllers\Api\FeedbackController::class, 'send');
$router->post('/api/include-area/save/', \Controllers\Api\IncludeAreaController::class, 'save');
$router->post('/api/component/settings/save/', \Controllers\Api\ComponentController::class, 'saveSettings');
$router->get('/api/images/', \Controllers\Api\ImagesController::class, 'list');
$router->get('/api/gallery/', \Controllers\Api\GalleryController::class, 'list');
$router->get('/api/blog/rubrics/', \Controllers\Api\BlogController::class, 'rubrics');
$router->get('/api/blog/articles/', \Controllers\Api\BlogController::class, 'articles');
$router->post('/api/blog/media/', \Controllers\Api\BlogController::class, 'uploadMedia');
$router->get('/api/blog/{topic}/{article}/', \Controllers\Api\BlogController::class, 'article');
$router->addRoute('OPTIONS', '/api/blog/rubrics/', \Controllers\Api\BlogController::class, 'options');
$router->addRoute('OPTIONS', '/api/blog/articles/', \Controllers\Api\BlogController::class, 'options');
$router->addRoute('OPTIONS', '/api/blog/media/', \Controllers\Api\BlogController::class, 'options');
$router->addRoute('OPTIONS', '/api/blog/{topic}/{article}/', \Controllers\Api\BlogController::class, 'options');
$router->post('/api/auth/login/', \Controllers\AuthController::class, 'login');
$router->post('/api/auth/register/', \Controllers\AuthController::class, 'register');
