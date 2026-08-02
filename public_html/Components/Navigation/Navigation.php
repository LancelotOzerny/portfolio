<?php
namespace Components\Navigation;

use Modules\Main\App;
use Modules\Main\Config;

class Navigation extends \Modules\Main\BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$params['type'] ??= 'main';

		$config = Config::getInstance()->get('Nav', $params['type']);
		$links = $config->toArray();

		foreach ($links as &$link)
		{
			$link['active'] = $this->isActiveLink($link);
		}

		$this->setParam('items', $links);
	}

	private function isActiveLink(array $link): bool
	{
		$currentPath = parse_url(App::getInstance()->page, PHP_URL_PATH) ?? '/';
		$linkPath = (string) ($link['link'] ?? '');

		if ($currentPath === $linkPath)
		{
			return true;
		}

		if ($linkPath !== '/' && $linkPath !== '' && str_starts_with($currentPath, $linkPath))
		{
			return true;
		}

		foreach ($link['children'] ?? [] as $child)
		{
			if ($currentPath === ($child['link'] ?? ''))
			{
				return true;
			}
		}

		return false;
	}
}
