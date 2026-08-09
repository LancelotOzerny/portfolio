<?php

namespace Controllers\Admin;

use Models\BlogArticlesModel;
use Models\BlogTopicsModel;
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

		$articlesModel = new BlogArticlesModel();

		$data = [
			'projectsCount' => (new ProjectsModel())->countAll(),
			'usersCount' => (new UsersModel())->countAll(),
			'rubricsCount' => (new BlogTopicsModel())->countAll(),
			'articlesCount' => $articlesModel->countAll(),
			'blogViewsWeekCount' => $articlesModel->countViewsSince(date('Y-m-d H:i:s', strtotime('-7 days'))),
			'blogViewsMonthCount' => $articlesModel->countViewsSince(date('Y-m-d H:i:s', strtotime('-30 days'))),
		];

        Template::getInstance()->showHeader();
        $this->render('index', $data);
        Template::getInstance()->showFooter();
    }
}
