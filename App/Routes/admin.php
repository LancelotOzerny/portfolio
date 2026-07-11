<?php
use Modules\Main\Router;

$router = Router::getInstance();

$router->get('/admin/login/', \Controllers\Admin\AuthController::class, 'login');
$router->get('/admin/', \Controllers\Admin\HomeController::class, 'index');
$router->get('/admin/projects/', \Controllers\Admin\ProjectsController::class, 'index');
$router->get('/admin/content/tags/', \Controllers\Admin\TagsController::class, 'index');
$router->post('/admin/content/tags/create/', \Controllers\Admin\TagsController::class, 'create');
$router->get('/admin/content/tags/{id}/', \Controllers\Admin\TagsController::class, 'edit');
$router->post('/admin/content/tags/{id}/', \Controllers\Admin\TagsController::class, 'update');
$router->post('/admin/content/tags/{id}/delete/', \Controllers\Admin\TagsController::class, 'delete');
$router->get('/admin/seo/', \Controllers\Admin\SeoController::class, 'index');
$router->get('/admin/seo/{pageKey}/', \Controllers\Admin\SeoController::class, 'edit');
$router->post('/admin/seo/{pageKey}/', \Controllers\Admin\SeoController::class, 'update');
$router->post('/admin/seo/{pageKey}/reset/', \Controllers\Admin\SeoController::class, 'reset');
$router->get('/admin/users/', \Controllers\Admin\UsersController::class, 'index');
$router->get('/admin/settings/', \Controllers\Admin\SettingsController::class, 'index');
$router->get('/admin/settings/configs/', \Controllers\Admin\ConfigsController::class, 'index');
$router->post('/admin/settings/configs/save/', \Controllers\Admin\ConfigsController::class, 'save');
$router->get('/admin/settings/repository/', \Controllers\Admin\RepositoryController::class, 'index');
$router->post('/admin/settings/repository/update/', \Controllers\Admin\RepositoryController::class, 'update');
$router->get('/admin/settings/backup/', \Controllers\Admin\BackupController::class, 'index');
$router->get('/admin/settings/backup/create/', \Controllers\Admin\BackupController::class, 'create');
$router->post('/admin/settings/backup/create/', \Controllers\Admin\BackupController::class, 'store');
$router->get('/admin/settings/backup/list/', \Controllers\Admin\BackupController::class, 'list');
$router->post('/admin/settings/backup/upload/', \Controllers\Admin\BackupController::class, 'upload');
$router->post('/admin/settings/backup/delete/{file}/', \Controllers\Admin\BackupController::class, 'delete');
$router->post('/admin/settings/backup/restore/{file}/', \Controllers\Admin\BackupController::class, 'restore');
$router->get('/admin/settings/backup/download/{file}/', \Controllers\Admin\BackupController::class, 'download');
$router->post('/admin/projects/create/', \Controllers\Admin\ProjectsController::class, 'create');
$router->get('/admin/projects/{id}/', \Controllers\Admin\ProjectsController::class, 'detail');
$router->post('/admin/projects/{id}/', \Controllers\Admin\ProjectsController::class, 'updateMainInfo');
$router->get('/admin/resume/experience/', \Controllers\Admin\ResumeController::class, 'experience');
$router->post('/admin/resume/experience/create/', \Controllers\Admin\ResumeController::class, 'createExperience');
$router->get('/admin/resume/experience/{id}/', \Controllers\Admin\ResumeController::class, 'editExperience');
$router->post('/admin/resume/experience/{id}/', \Controllers\Admin\ResumeController::class, 'updateExperience');
