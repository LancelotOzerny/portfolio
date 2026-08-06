<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;
use Throwable;

class BlogArticlesModel extends BaseModel
{
	protected string $table = 'blog_articles';

	public function createForAdmin(int $topicId, string $title, string $code): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'topic_id' => $topicId,
			'title' => $title,
			'code' => $code,
			'enabled' => 0,
			'preview_text' => '',
			'preview_image_path' => '',
			'detail_text' => '',
			'detail_image_path' => '',
			'author' => '',
		]);

		return $this->execInsertQuery($qb);
	}

	public function updateEditorData(
		int $id,
		int $topicId,
		string $title,
		string $code,
		int $enabled,
		string $previewText,
		string $previewImagePath,
		string $detailText,
		string $detailImagePath,
		string $author
	): bool {
		$qb = (new QueryBuilder($this->table))
			->update([
				'topic_id' => $topicId,
				'title' => $title,
				'code' => $code,
				'enabled' => $enabled,
				'preview_text' => $previewText,
				'preview_image_path' => $previewImagePath,
				'detail_text' => $detailText,
				'detail_image_path' => $detailImagePath,
				'author' => $author,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function findByCode(string $code): ?object
	{
		$code = trim($code);
		if ($code === '') {
			return null;
		}

		return $this->findBy('code', $code);
	}

	public function isCodeTaken(string $code, ?int $excludeId = null): bool
	{
		$code = trim($code);
		if ($code === '') {
			return false;
		}

		$qb = (new QueryBuilder($this->table))
			->select(['id'])
			->where('code', '=', $code);

		if ($excludeId !== null && $excludeId > 0) {
			$qb->where('id', '!=', $excludeId);
		}

		return $this->execQuery($qb, true) !== null;
	}

	public function deleteById(int $id): bool
	{
		$this->replaceTopicIds($id, []);

		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function findAllWithTopic(): array
	{
		try {
			return $this->findAllWithTopicRelations();
		} catch (Throwable) {
			return $this->findAllWithTopicLegacy();
		}
	}

	public function findActiveByTopicId(int $topicId): array
	{
		return $this->findByTopicId($topicId, true);
	}

	public function findByTopicId(int $topicId, bool $onlyActive = true): array
	{
		$articles = [];
		$seenIds = [];

		foreach ([
			$this->safeFindByTopicIdViaRelations($topicId, $onlyActive),
			$this->safeFindByTopicIdLegacy($topicId, $onlyActive),
		] as $batch) {
			foreach ($batch as $article) {
				$articleId = (int) ($article->id ?? 0);
				if ($articleId <= 0 || isset($seenIds[$articleId])) {
					continue;
				}

				$seenIds[$articleId] = true;
				$articles[] = $article;
			}
		}

		usort($articles, static function (object $left, object $right): int {
			return (int) ($right->id ?? 0) <=> (int) ($left->id ?? 0);
		});

		return $articles;
	}

	public function findLatestActive(int $limit): array
	{
		try {
			$articles = $this->findLatestActiveViaRelations($limit);
			if ($articles !== []) {
				return $articles;
			}
		} catch (Throwable) {
		}

		try {
			return $this->findLatestActiveLegacy($limit);
		} catch (Throwable) {
			return [];
		}
	}

	public function findTopicIdsByArticleId(int $articleId): array
	{
		try {
			$qb = (new QueryBuilder('blog_article_topic_relations'))
				->select(['topic_id'])
				->where('article_id', '=', $articleId)
				->orderBy('topic_id', 'ASC');

			$items = $this->execQuery($qb) ?? [];
		} catch (Throwable) {
			$article = $this->findById($articleId);
			$topicId = (int) ($article->topic_id ?? 0);

			return $topicId > 0 ? [$topicId] : [];
		}

		$result = [];

		foreach ($items as $item) {
			$topicId = (int) ($item->topic_id ?? 0);
			if ($topicId > 0) {
				$result[] = $topicId;
			}
		}

		return array_values(array_unique($result));
	}

	public function registerUniqueView(int $articleId, string $ipAddress): bool
	{
		$ipAddress = trim($ipAddress);
		if ($articleId <= 0 || $ipAddress === '') {
			return false;
		}

		try {
			$insert = (new QueryBuilder('blog_article_views'))->insert([
				'article_id' => $articleId,
				'ip_address' => $ipAddress,
			]);

			if ($this->execInsertQuery($insert) <= 0) {
				return false;
			}
		} catch (Throwable) {
			return false;
		}

		$increment = (new QueryBuilder($this->table))->raw(
			'UPDATE ' . $this->table . ' SET views_count = views_count + 1 WHERE id = :article_id',
			['article_id' => $articleId]
		);

		return $this->execWriteQuery($increment);
	}

	public function replaceTopicIds(int $articleId, array $topicIds): bool
	{
		try {
			$delete = (new QueryBuilder('blog_article_topic_relations'))
				->delete()
				->where('article_id', '=', $articleId);

			if (!$this->execWriteQuery($delete)) {
				return false;
			}

			foreach (array_values(array_unique($topicIds)) as $topicId) {
				$topicId = (int) $topicId;
				if ($topicId <= 0) {
					continue;
				}

				$insert = (new QueryBuilder('blog_article_topic_relations'))->insert([
					'article_id' => $articleId,
					'topic_id' => $topicId,
				]);

				if (!$this->execWriteQuery($insert)) {
					return false;
				}
			}

			return true;
		} catch (Throwable) {
			$topicId = (int) ($topicIds[0] ?? 0);
			if ($topicId <= 0) {
				return true;
			}

			$qb = (new QueryBuilder($this->table))
				->update(['topic_id' => $topicId])
				->where('id', '=', $articleId);

			return $this->execWriteQuery($qb);
		}
	}

	private function findAllWithTopicRelations(): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*, GROUP_CONCAT(DISTINCT blog_topics.title ORDER BY blog_topics.title SEPARATOR ", ") AS topic_title')
			->join('blog_article_topic_relations', 'blog_articles.id', 'blog_article_topic_relations.article_id', 'LEFT')
			->join('blog_topics', 'blog_article_topic_relations.topic_id', 'blog_topics.id', 'LEFT')
			->groupBy('blog_articles.id')
			->orderBy('blog_articles.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	private function findAllWithTopicLegacy(): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*, blog_topics.title AS topic_title')
			->join('blog_topics', 'blog_articles.topic_id', 'blog_topics.id', 'LEFT')
			->orderBy('blog_articles.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	private function findByTopicIdViaRelations(int $topicId, bool $onlyActive): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*')
			->join('blog_article_topic_relations', 'blog_articles.id', 'blog_article_topic_relations.article_id', 'INNER')
			->where('blog_article_topic_relations.topic_id', '=', $topicId);

		if ($onlyActive) {
			$qb->where('blog_articles.enabled', '=', 1);
		}

		$qb->groupBy('blog_articles.id')
			->orderBy('blog_articles.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	private function findByTopicIdLegacy(int $topicId, bool $onlyActive): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*')
			->where('topic_id', '=', $topicId);

		if ($onlyActive) {
			$qb->where('enabled', '=', 1);
		}

		$qb->orderBy('blog_articles.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	private function safeFindByTopicIdViaRelations(int $topicId, bool $onlyActive): array
	{
		try {
			return $this->findByTopicIdViaRelations($topicId, $onlyActive);
		} catch (Throwable) {
			return [];
		}
	}

	private function safeFindByTopicIdLegacy(int $topicId, bool $onlyActive): array
	{
		try {
			return $this->findByTopicIdLegacy($topicId, $onlyActive);
		} catch (Throwable) {
			return [];
		}
	}

	private function findLatestActiveViaRelations(int $limit): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*, MIN(blog_topics.id) AS topic_id_resolved, MIN(blog_topics.title) AS topic_title, MIN(blog_topics.code) AS topic_code')
			->join('blog_article_topic_relations', 'blog_articles.id', 'blog_article_topic_relations.article_id', 'INNER')
			->join('blog_topics', 'blog_article_topic_relations.topic_id', 'blog_topics.id', 'INNER')
			->where('blog_articles.enabled', '=', 1)
			->where('blog_topics.enabled', '=', 1)
			->groupBy('blog_articles.id')
			->orderBy('blog_articles.created_at', 'DESC')
			->orderBy('blog_articles.id', 'DESC');

		if ($limit > 0) {
			$qb->limit($limit);
		}

		return $this->execQuery($qb) ?? [];
	}

	private function findLatestActiveLegacy(int $limit): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*, blog_articles.topic_id AS topic_id_resolved, blog_topics.title AS topic_title, blog_topics.code AS topic_code')
			->join('blog_topics', 'blog_articles.topic_id', 'blog_topics.id', 'INNER')
			->where('blog_articles.enabled', '=', 1)
			->where('blog_topics.enabled', '=', 1)
			->orderBy('blog_articles.created_at', 'DESC')
			->orderBy('blog_articles.id', 'DESC');

		if ($limit > 0) {
			$qb->limit($limit);
		}

		return $this->execQuery($qb) ?? [];
	}
}
