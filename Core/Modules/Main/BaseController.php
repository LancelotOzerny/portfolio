<?php

namespace Modules\Main;

use App\Services\Seo\SeoContext;
use App\Services\Seo\SeoService;

class BaseController
{
	protected function setSeo(SeoContext $context): void
	{
		Template::getInstance()->setParam('seo', (new SeoService())->resolve($context));
	}

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

		echo '<pre>';
		print_r($viewPath);
		echo '</pre>';

		echo "View '$view' not found";
	}
}