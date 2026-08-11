<?php

namespace App\Services\Blog;

use App\Services\Seo\SeoValidator;
use Models\SeoMetaModel;
use Throwable;

/**
 * SEO для сущностей блога (рубрики и статьи).
 * OG-поля не хранятся отдельно — берутся из preview.
 */
final class BlogSeoService
{
	public const TYPE_TOPIC = 'blog_topic';
	public const TYPE_ARTICLE = 'blog_article';

	public function __construct(
		private readonly SeoMetaModel $model = new SeoMetaModel(),
		private readonly SeoValidator $validator = new SeoValidator(),
	) {
	}

	/**
	 * @return array{
	 *   title: string,
	 *   description: string,
	 *   keywords: string,
	 *   robots_index: bool,
	 *   robots_follow: bool
	 * }
	 */
	public function getFormData(string $type, string $key): array
	{
		$record = null;

		try {
			$record = $this->model->findByTarget($type, $key);
		} catch (Throwable) {
			$record = null;
		}

		return [
			'title' => (string) ($record->title ?? ''),
			'description' => (string) ($record->description ?? ''),
			'keywords' => (string) ($record->keywords ?? ''),
			'robots_index' => $record === null ? true : (int) ($record->robots_index ?? 1) === 1,
			'robots_follow' => $record === null ? true : (int) ($record->robots_follow ?? 1) === 1,
		];
	}

	/**
	 * Сохранение SEO из админ-формы (поля seo_*).
	 *
	 * @param array<string, mixed> $post
	 */
	public function saveFromAdminPost(string $type, string $key, array $post): void
	{
		$fields = $this->validator->validateBlogSeoForm([
			'title' => $post['seo_title'] ?? null,
			'description' => $post['seo_description'] ?? null,
			'keywords' => $post['seo_keywords'] ?? null,
		]);

		$this->persist($type, $key, $fields, [
			'robots_index' => isset($post['seo_robots_index']) ? 1 : 0,
			'robots_follow' => isset($post['seo_robots_follow']) ? 1 : 0,
		]);
	}

	/**
	 * Сохранение SEO из публичной модалки.
	 *
	 * @param array{title: ?string, description: ?string, keywords: ?string} $fields
	 */
	public function saveFromPublicFields(string $type, string $key, array $fields): void
	{
		$existing = null;

		try {
			$existing = $this->model->findByTarget($type, $key);
		} catch (Throwable) {
			$existing = null;
		}

		$this->persist($type, $key, $fields, [
			'robots_index' => (int) ($existing->robots_index ?? 1),
			'robots_follow' => (int) ($existing->robots_follow ?? 1),
		]);
	}

	/**
	 * @param array{title: ?string, description: ?string, keywords: ?string} $fields
	 * @param array{robots_index: int, robots_follow: int} $robots
	 */
	private function persist(string $type, string $key, array $fields, array $robots): void
	{
		$existing = null;

		try {
			$existing = $this->model->findByTarget($type, $key);
		} catch (Throwable) {
			$existing = null;
		}

		$payload = [
			'title' => $fields['title'],
			'description' => $fields['description'],
			'keywords' => $fields['keywords'],
			'canonical_url' => $existing->canonical_url ?? null,
			'robots_index' => $robots['robots_index'],
			'robots_follow' => $robots['robots_follow'],
			// OG всегда из preview — не храним отдельно
			'og_title' => null,
			'og_description' => null,
			'og_image' => null,
		];

		if (!$this->model->saveByTarget($type, $key, $payload)) {
			throw new \RuntimeException('Не удалось сохранить SEO.');
		}
	}
}
