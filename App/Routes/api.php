<?php
use Modules\Main\Router;
$router = Router::getInstance();

$router->get('/api/users/', \Controllers\Api\UserController::class, 'getAll');
$router->post('/api/feedback/send/', \Controllers\Api\FeedbackController::class, 'send');