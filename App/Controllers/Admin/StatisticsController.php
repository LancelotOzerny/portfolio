<?php

namespace Controllers\Admin;

use Models\BlogArticleCommentsModel;
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
		$commentsModel = new BlogArticleCommentsModel();
		$weekSince = date('Y-m-d H:i:s', strtotime('-7 days'));
		$monthSince = date('Y-m-d H:i:s', strtotime('-30 days'));
		$yearSince = date('Y-m-d H:i:s', strtotime('-1 year'));

		$data = [
			'topRubricsAllTime' => $articlesModel->findTopViewedTopics(null, 3),
			'topRubricsMonth' => $articlesModel->findTopViewedTopics($monthSince, 3),
			'topArticlesWeek' => $articlesModel->findTopViewedArticles($weekSince, 10),
			'topArticlesMonth' => $articlesModel->findTopViewedArticles($monthSince, 15),
			'topArticlesAllTime' => $articlesModel->findTopViewedArticles(null, 15),
			'topCommentedWeek' => $commentsModel->findTopCommentedArticles($weekSince, 5),
			'topCommentedMonth' => $commentsModel->findTopCommentedArticles($monthSince, 5),
			'topCommentedYear' => $commentsModel->findTopCommentedArticles($yearSince, 5),
			'topRatedArticles' => $articlesModel->findTopRatedArticles(21),
		];

		Template::getInstance()->showHeader();
		$this->render('blog', $data);
		Template::getInstance()->showFooter();
	}
}
