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
$loader->addPath('Models\\', $app->root . '/App/Models');
$loader->addPath('Develop\\', $app->root . '/Develop');
$loader->addPath('App\\', $app->root . '/App');
$loader->addPath('Components\\', $app->root . '/public_html/Components');
$loader->register();

\Modules\Main\App::getInstance()->init();

$match = Router::getInstance()->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

if (!$match)
{
	$match = [\Controllers\Public\StatusController::class, 'page404', []];
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

$cssLines = \Modules\Main\AssetLoader::getInstance()->getCssLines();
$jsLines = \Modules\Main\AssetLoader::getInstance()->getJsLines();

$html = str_replace('</body>', $jsLines . '</body>', $html);
$html = str_replace('</head>', $cssLines . '</head>', $html);

echo $html;