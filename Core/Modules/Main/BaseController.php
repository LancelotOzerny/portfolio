<?php

namespace Modules\Main;

class BaseController
{
	protected function render(string $view, array $data = [])
	{
		$className = static::class;
		$withoutPrefix = preg_replace('~^Controllers\\\\~', '', $className);
		$controllerName = preg_replace('~Controller$~', '', $withoutPrefix);
		$viewPath = App::getInstance()->root . '/App/Views/' . $controllerName . '/' . $view . '.php';
		$viewPath = str_replace('\\', '/', $viewPath);

		if (file_exists($viewPath))
		{
			include $viewPath;
			return;
		}

		echo "View '$view' not found";
	}
}