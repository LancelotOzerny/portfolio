<?php

namespace App\Listeners\Admin;

use App\Events\Admin\AdminBarBuildEvent;
use App\Services\Admin\Bar\AdminBarAction;
use App\Services\Admin\Bar\AdminBarGroup;
use Models\BlogArticlesModel;
use Models\BlogTopicsModel;
use Throwable;

/**
 * Кнопки настроек блога в центре AdminBar на страницах рубрики/статьи.
 */
final class BlogAdminBarListener
{
	public function __invoke(AdminBarBuildEvent $event): void
	{
		if (!$event->isEditMode()) {
			return;
		}

		$path = rtrim($event->getCurrentPath(), '/') . '/';
		if ($path === '/blog/' || !str_starts_with($path, '/blog/')) {
			return;
		}

		if (!$this->canEditCurrentBlogPage($path)) {
			return;
		}

		$event->addGroup(new AdminBarGroup(
			id: 'blog',
			label: 'Блог',
			actions: [
				new AdminBarAction(
					id: 'blog.basic',
					label: 'Базовая информация',
					attributes: [
						'data-blog-settings-open' => 'basic',
						'type' => 'button',
					],
				),
				new AdminBarAction(
					id: 'blog.seo',
					label: 'SEO',
					attributes: [
						'data-blog-settings-open' => 'seo',
						'type' => 'button',
					],
				),
			],
		));
	}

	private function canEditCurrentBlogPage(string $path): bool
	{
		if (preg_match('#^/blog/([^/]+)/([^/]+)/$#', $path, $matches) === 1) {
			return $this->resolveArticle($matches[2]) !== null;
		}

		if (preg_match('#^/blog/([^/]+)/$#', $path, $matches) === 1) {
			return $this->resolveTopic($matches[1]) !== null;
		}

		return false;
	}

	private function resolveTopic(string $slug): ?object
	{
		$model = new BlogTopicsModel();

		try {
			if (ctype_digit($slug)) {
				$topic = $model->findById((int) $slug);
				if ($topic !== null) {
					return $topic;
				}
			}

			return $model->findByCode($slug);
		} catch (Throwable) {
			return null;
		}
	}

	private function resolveArticle(string $slug): ?object
	{
		$model = new BlogArticlesModel();

		try {
			if (ctype_digit($slug)) {
				$article = $model->findById((int) $slug);
				if ($article !== null) {
					return $article;
				}
			}

			return $model->findByCode($slug);
		} catch (Throwable) {
			return null;
		}
	}
}
