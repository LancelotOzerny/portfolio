<?php

namespace Controllers\Admin;

use Models\ProjectsModel;
use Models\UsersModel;
use Modules\Main\BaseController;
use Modules\Main\Auth;
use Modules\Main\Template;

class HomeController extends BaseController
{
    public function index(): void
    {
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			header('Location: /admin/login/');
			return;
		}

        Template::getInstance()->setParam('title', 'Панель администратора');

		$data = [
			'projectsCount' => (new ProjectsModel())->countAll(),
			'usersCount' => (new UsersModel())->countAll(),
		];

        Template::getInstance()->showHeader();
        $this->render('index', $data);
        Template::getInstance()->showFooter();
    }
}
