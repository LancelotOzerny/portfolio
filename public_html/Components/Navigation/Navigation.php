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

		foreach($links as &$link)
		{
			if (App::getInstance()->page === $link['link'])
			{
				$link['active'] = true;
				continue;
			}
			$link['active'] = false;
		}

		$this->setParam('items', $links);
	}
}