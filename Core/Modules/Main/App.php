<?php

namespace Modules\Main;

use App\Events\Page\PageBuildEndEvent;
use App\Events\Page\PageBuildStartEvent;
use Modules\Main\Event\EventDispatcher;
use Modules\Main\ViewData;

class App
{
    public readonly string $root;
    public readonly string $page;
    private static ?self $instance = null;

    private function __construct()
    {
        $this->root = dirname($_SERVER['DOCUMENT_ROOT']);
		$this->page = $_SERVER['REQUEST_URI'];
    }

    public static function getInstance(): self
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

	public function init() : void
	{
		$this->requireRoutes();
		$this->requireEventListeners();
		(new \App\Services\Site\EditModeService())->handleRequest();
	}

	public function start() : void
	{
		$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
		$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
		$path = parse_url($uri, PHP_URL_PATH) ?: '/';

		$match = Router::getInstance()->dispatch($method, $uri);

		if (!$match)
		{
			$match = [\Controllers\Public\StatusController::class, 'page404', []];
		}

		/* ######################## PREPARE PAGE ######################## */
		[$controllerClass, $action, $paramsAssoc] = $match;
		$controller = new $controllerClass();
		$params = array_values($paramsAssoc);

		EventDispatcher::getInstance()->dispatch(new PageBuildStartEvent(
			$method,
			$path,
			$controllerClass,
			$action,
			$paramsAssoc
		));

		ob_start();
		call_user_func_array([$controller, $action], $params);
		$html = ob_get_clean();

		$viewData = ViewData::getInstance();
		$html = $viewData->replacePlaceholders($html);

		$cssLines = \Modules\Main\AssetLoader::getInstance()->getCssLines();
		$jsLines = \Modules\Main\AssetLoader::getInstance()->getJsLines();

		$html = str_replace('</body>', $jsLines . '</body>', $html);
		$html = str_replace('</head>', $cssLines . '</head>', $html);

		$endEvent = new PageBuildEndEvent(
			$method,
			$path,
			$controllerClass,
			$action,
			$paramsAssoc,
			$html
		);
		EventDispatcher::getInstance()->dispatch($endEvent);

		echo $endEvent->getHtml();
	}


	protected function requireRoutes() : void
	{
		$folder = $this->root . '/App/Routes';
		$files = scandir($folder);

		foreach ($files as $file)
		{
			if (str_ends_with($file, '.php'))
			{
				require_once "{$folder}/{$file}";
			}
		}
	}

	protected function requireEventListeners() : void
	{
		$file = $this->root . '/App/Events/listeners.php';

		if (is_file($file)) {
			require_once $file;
		}
	}
}
