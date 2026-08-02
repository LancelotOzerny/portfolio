<?php
use Modules\Main\Router;
$router = Router::getInstance();

$router->get('/', \Controllers\Public\HomeController::class,  'index');
$router->get('/about/', \Controllers\Public\AboutController::class,  'index');
$router->get('/projects/', \Controllers\Public\PortfolioController::class,  'index');
$router->get('/portfolio/', \Controllers\Public\PortfolioController::class,  'index');
$router->get('/portfolio/{id}/', \Controllers\Public\PortfolioController::class,  'detail');
$router->get('/blog/', \Controllers\Public\BlogController::class,  'index');
$router->get('/blog/{topic}/', \Controllers\Public\BlogController::class,  'topic');
$router->get('/blog/{topic}/{article}/', \Controllers\Public\BlogController::class,  'detail');
$router->post('/blog/{topic}/{article}/save/', \Controllers\Public\BlogController::class,  'updateDetail');
$router->post('/blog/{topic}/{article}/image/', \Controllers\Public\BlogController::class,  'uploadDetailImage');
$router->get('/certificates/', \Controllers\Public\CertificatesController::class,  'index');
$router->get('/contacts/', \Controllers\Public\ContactsController::class,  'index');
$router->get('/auth/logout/', \Controllers\AuthController::class, 'logout');
