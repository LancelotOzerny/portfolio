<?php

namespace Modules\Main;

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
	}

	public function start() : void
	{
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
}