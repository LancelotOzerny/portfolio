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
		if (App::getInstance()->page === ($link['link'] ?? ''))
		{
			return true;
		}

		foreach ($link['children'] ?? [] as $child)
		{
			if (App::getInstance()->page === ($child['link'] ?? ''))
			{
				return true;
			}
		}

		return false;
	}
}
