<?php
use Modules\Main\Router;
$router = Router::getInstance();

$router->get('/', \Controllers\Public\HomeController::class,  'index');
$router->get('/about/', \Controllers\Public\AboutController::class,  'index');
$router->get('/portfolio/', \Controllers\Public\PortfolioController::class,  'index');
$router->get('/certificates/', \Controllers\Public\CertificatesController::class,  'index');
$router->get('/contacts/', \Controllers\Public\ContactsController::class,  'index');