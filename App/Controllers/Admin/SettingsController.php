<?php

namespace Controllers\Admin;

use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;

class SettingsController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Настройки');

		Template::getInstance()->showHeader();
		$this->render('index');
		Template::getInstance()->showFooter();
	}

	private function ensureAdmin(): bool
	{
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			header('Location: /admin/login/');
			return false;
		}

		return true;
	}
}

