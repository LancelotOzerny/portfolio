<?php

namespace App\Listeners\Admin;

use App\Events\Admin\AdminBarBuildEvent;
use App\Services\Admin\Bar\AdminBarAction;
use App\Services\Admin\Bar\AdminBarGroup;
use App\Services\Blog\BlogArticlePublicationService;
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

		if (preg_match('#^/blog/([^/]+)/([^/]+)/$#', $path, $matches) === 1) {
			$article = $this->resolveArticle($matches[2]);
			if ($article === null) {
				return;
			}

			$articleId = (int) ($article->id ?? 0);
			if ($articleId <= 0) {
				return;
			}

			$actions = [
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
			];

			if (!(new BlogArticlePublicationService())->isPublished($article)) {
				$actions[] = new AdminBarAction(
					id: 'blog.publish',
					label: 'Опубликовать',
					attributes: [
						'data-blog-publish' => (string) $articleId,
						'type' => 'button',
					],
				);
				$actions[] = new AdminBarAction(
					id: 'blog.schedule',
					label: 'Опубликовать потом',
					attributes: [
						'data-blog-schedule-open' => (string) $articleId,
						'type' => 'button',
					],
				);
			}

			$event->addGroup(new AdminBarGroup(
				id: 'blog',
				label: 'Блог',
				actions: $actions,
			));

			return;
		}

		if (preg_match('#^/blog/([^/]+)/$#', $path, $matches) === 1) {
			if ($this->resolveTopic($matches[1]) === null) {
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
