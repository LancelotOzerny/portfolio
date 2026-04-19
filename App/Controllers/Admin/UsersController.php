<?php

namespace Controllers\Admin;

use Models\UsersModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class UsersController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Список пользователей');

		$users = [];
		try {
			$users = (new UsersModel())->findAllWithRights();
		} catch (Throwable) {
			$users = [];
		}

		Template::getInstance()->showHeader();
		$this->render('index', [
			'users' => $users,
		]);
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

