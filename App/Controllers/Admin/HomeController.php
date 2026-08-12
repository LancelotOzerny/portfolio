<?php

namespace Controllers\Admin;

use Models\BlogArticleCommentsModel;
use Models\BlogArticlesModel;
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
		$weekSince = date('Y-m-d H:i:s', strtotime('-7 days'));
		$monthSince = date('Y-m-d H:i:s', strtotime('-30 days'));

		$data = [
			'projectsCount' => (new ProjectsModel())->countAll(),
			'usersCount' => (new UsersModel())->countAll(),
			'articlesCount' => $articlesModel->countAll(),
			'blogViewsWeekCount' => $articlesModel->countViewsSince($weekSince),
			'blogViewsMonthCount' => $articlesModel->countViewsSince($monthSince),
			'blogCommentsMonthCount' => (new BlogArticleCommentsModel())->countSince($monthSince),
		];

        Template::getInstance()->showHeader();
        $this->render('index', $data);
        Template::getInstance()->showFooter();
    }
}
