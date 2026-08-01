<?php

namespace Components\SocialNetworks;

use Modules\Main\BaseComponent;
use Modules\Main\Config;

class SocialNetworks extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$networks = Config::getInstance()->get('App', 'networks')->toArray();

		$this->setParam('items', $networks);
	}
}
