<?php

namespace Controllers\Admin;

use Models\BlogArticlesModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;

class StatisticsController extends BaseController
{
	public function blog(): void
	{
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			header('Location: /admin/login/');
			return;
		}

		Template::getInstance()->setParam('title', 'Статистика блога');

		$articlesModel = new BlogArticlesModel();
		$weekSince = date('Y-m-d H:i:s', strtotime('-7 days'));
		$monthSince = date('Y-m-d H:i:s', strtotime('-30 days'));

		$data = [
			'topArticlesWeek' => $articlesModel->findTopViewedArticles($weekSince, 5),
			'topArticlesMonth' => $articlesModel->findTopViewedArticles($monthSince, 5),
			'topArticlesAllTime' => $articlesModel->findTopViewedArticles(null, 5),
			'topRubricWeek' => $articlesModel->findTopViewedTopic($weekSince),
			'topRubricMonth' => $articlesModel->findTopViewedTopic($monthSince),
			'topRubricAllTime' => $articlesModel->findTopViewedTopic(null),
		];

		Template::getInstance()->showHeader();
		$this->render('blog', $data);
		Template::getInstance()->showFooter();
	}
}
