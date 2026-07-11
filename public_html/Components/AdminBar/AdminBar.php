<?php

namespace Components\AdminBar;

use Modules\Main\Auth;
use Modules\Main\BaseComponent;

class AdminBar extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
		$isAdminArea = str_starts_with($currentPath, '/admin/');

		$this->setParam('show', Auth::getInstance()->isAdmin() && !$isAdminArea);
		$this->setParam('back_url', $currentPath);
	}
}
