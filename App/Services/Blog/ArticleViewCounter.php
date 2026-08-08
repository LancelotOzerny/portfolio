<?php

namespace App\Services\Blog;

use Models\BlogArticlesModel;
use Throwable;

class ArticleViewCounter
{
	private BlogArticlesModel $articlesModel;
	private BlogViewerIdentity $viewerIdentity;

	public function __construct(
		?BlogArticlesModel $articlesModel = null,
		?BlogViewerIdentity $viewerIdentity = null
	) {
		$this->articlesModel = $articlesModel ?? new BlogArticlesModel();
		$this->viewerIdentity = $viewerIdentity ?? new BlogViewerIdentity();
	}

	public function registerIfUnique(int $articleId): bool
	{
		if ($articleId <= 0) {
			return false;
		}

		$viewerKey = $this->viewerIdentity->resolveKey();
		if ($viewerKey === '') {
			return false;
		}

		try {
			return $this->articlesModel->registerUniqueView(
				$articleId,
				$viewerKey,
				$this->viewerIdentity->resolveClientIp()
			);
		} catch (Throwable) {
			return false;
		}
	}
}
