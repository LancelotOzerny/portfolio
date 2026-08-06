<?php

namespace App\Services\Blog;

use Models\BlogArticlesModel;
use Throwable;

class ArticleViewCounter
{
	private BlogArticlesModel $articlesModel;

	public function __construct(?BlogArticlesModel $articlesModel = null)
	{
		$this->articlesModel = $articlesModel ?? new BlogArticlesModel();
	}

	public function registerIfUnique(int $articleId): bool
	{
		if ($articleId <= 0) {
			return false;
		}

		$ipAddress = $this->resolveClientIp();
		if ($ipAddress === '') {
			return false;
		}

		try {
			return $this->articlesModel->registerUniqueView($articleId, $ipAddress);
		} catch (Throwable) {
			return false;
		}
	}

	private function resolveClientIp(): string
	{
		$ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
		if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
			return '';
		}

		return $ip;
	}
}
