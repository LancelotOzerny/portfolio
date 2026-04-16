<?php

namespace Controllers\Admin;

use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;

class AuthController extends BaseController
{
	public function login(): void
	{
		if (Auth::getInstance()->isAdmin()) {
			header('Location: /admin/');
			return;
		}

		Template::getInstance()->template = 'Admin';
		Template::getInstance()->setParam('title', 'Авторизация');

		Template::getInstance()->showHeader();
		$this->render('login');
		Template::getInstance()->showFooter();
	}
}
