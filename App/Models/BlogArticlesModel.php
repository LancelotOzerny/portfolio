<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;
use Throwable;

class BlogArticlesModel extends BaseModel
{
	protected string $table = 'blog_articles';

	public function createForAdmin(int $topicId, string $title, string $code, string $previewText = ''): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'topic_id' => $topicId,
			'title' => $title,
			'code' => $code,
			'enabled' => 0,
			'preview_text' => $previewText,
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

	public function updateBasicInfo(int $id, string $title, string $previewText): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update([
				'title' => $title,
				'preview_text' => $previewText,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function updatePreviewText(int $id, string $previewText): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update(['preview_text' => $previewText])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function updateDetailText(int $id, string $detailText): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update(['detail_text' => $detailText])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function updateImagePath(int $id, string $type, string $path): bool
	{
		$column = $type === 'detail' ? 'detail_image_path' : 'preview_image_path';
		$qb = (new QueryBuilder($this->table))
			->update([$column => $path])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function publishNow(int $id): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update([
				'enabled' => 1,
				'published_at' => date('Y-m-d H:i:s'),
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function schedulePublication(int $id, string $publishedAt): bool
	{
		$publishedAt = trim($publishedAt);
		if ($publishedAt === '') {
			return false;
		}

		$qb = (new QueryBuilder($this->table))
			->update([
				'enabled' => 0,
				'published_at' => $publishedAt,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function activateScheduled(int $id): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update(['enabled' => 1])
			->where('id', '=', $id)
			->where('enabled', '=', 0);

		return $this->execWriteQuery($qb);
	}

	/**
	 * @return list<object>
	 */
	public function findDueForPublication(): array
	{
		$qb = (new QueryBuilder($this->table))
			->select(['id'])
			->where('enabled', '=', 0)
			->whereNotNull('published_at')
			->where('published_at', '<=', date('Y-m-d H:i:s'))
			->orderBy('published_at', 'ASC');

		return $this->execQuery($qb) ?? [];
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
			$articles = $this->findAllWithTopicRelations();
		} catch (Throwable) {
			$articles = $this->findAllWithTopicLegacy();
		}

		return $this->sortForDisplay($articles);
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

		return $this->sortForDisplay($articles);
	}

	public function findLatestActive(int $limit): array
	{
		return $this->findLatest($limit, true);
	}

	public function findLatest(int $limit, bool $onlyActive = true): array
	{
		try {
			$articles = $this->findLatestViaRelations($limit, $onlyActive);
			if ($articles !== []) {
				return $articles;
			}
		} catch (Throwable) {
		}

		try {
			return $this->findLatestLegacy($limit, $onlyActive);
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

	public function registerUniqueView(int $articleId, string $viewerKey, string $ipAddress = ''): bool
	{
		$viewerKey = trim($viewerKey);
		$ipAddress = trim($ipAddress);
		if ($articleId <= 0 || $viewerKey === '') {
			return false;
		}

		if ($ipAddress === '' || filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
			$ipAddress = '0.0.0.0';
		}

		try {
			$insert = (new QueryBuilder('blog_article_views'))->insert([
				'article_id' => $articleId,
				'ip_address' => $ipAddress,
				'viewer_key' => $viewerKey,
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

	public function countViewsSince(string $sinceDatetime): int
	{
		$sinceDatetime = trim($sinceDatetime);
		if ($sinceDatetime === '') {
			return 0;
		}

		try {
			$qb = (new QueryBuilder('blog_article_views'))
				->count()
				->where('created_at', '>=', $sinceDatetime);

			$result = $this->execQuery($qb, true);
			if (!is_object($result)) {
				return 0;
			}

			return (int) ($result->total ?? 0);
		} catch (Throwable) {
			return 0;
		}
	}

	/**
	 * @return list<object>
	 */
	public function findTopViewedArticles(?string $sinceDatetime = null, int $limit = 5): array
	{
		$limit = max(1, $limit);

		try {
			$qb = (new QueryBuilder('blog_article_views'))
				->selectRaw('blog_articles.id AS id, blog_articles.title AS title, COUNT(blog_article_views.id) AS views_count')
				->join('blog_articles', 'blog_article_views.article_id', 'blog_articles.id', 'INNER');

			$this->applyViewsSinceFilter($qb, $sinceDatetime);

			$qb->groupBy(['blog_articles.id', 'blog_articles.title'])
				->orderBy('views_count', 'DESC')
				->orderBy('blog_articles.id', 'DESC')
				->limit($limit);

			return $this->execQuery($qb) ?? [];
		} catch (Throwable) {
			return [];
		}
	}

	public function findTopViewedTopic(?string $sinceDatetime = null): ?object
	{
		try {
			$topic = $this->findTopViewedTopicViaRelations($sinceDatetime);
			if ($topic !== null) {
				return $topic;
			}
		} catch (Throwable) {
		}

		try {
			return $this->findTopViewedTopicLegacy($sinceDatetime);
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * @return array{average: float, count: int}
	 */
	public function getRatingSummary(int $articleId): array
	{
		$summaries = $this->getRatingSummariesByArticleIds([$articleId]);

		return $summaries[$articleId] ?? [
			'average' => 0.0,
			'count' => 0,
		];
	}

	/**
	 * @param list<int> $articleIds
	 * @return array<int, array{average: float, count: int}>
	 */
	public function getRatingSummariesByArticleIds(array $articleIds): array
	{
		$ids = [];
		foreach ($articleIds as $articleId) {
			$articleId = (int) $articleId;
			if ($articleId > 0) {
				$ids[$articleId] = $articleId;
			}
		}

		if ($ids === []) {
			return [];
		}

		$qb = (new QueryBuilder('blog_article_ratings'))
			->selectRaw('article_id, AVG(rating) AS avg_rating, COUNT(*) AS votes_count')
			->whereIn('article_id', array_values($ids))
			->groupBy('article_id');

		$rows = $this->execQuery($qb) ?? [];
		$result = [];

		foreach ($rows as $row) {
			$articleId = (int) ($row->article_id ?? 0);
			if ($articleId <= 0) {
				continue;
			}

			$count = (int) ($row->votes_count ?? 0);
			$result[$articleId] = [
				'average' => $count > 0 ? round((float) ($row->avg_rating ?? 0), 1) : 0.0,
				'count' => $count,
			];
		}

		return $result;
	}

	public function findViewerRating(int $articleId, string $viewerKey): ?int
	{
		$viewerKey = trim($viewerKey);
		if ($articleId <= 0 || $viewerKey === '') {
			return null;
		}

		$qb = (new QueryBuilder('blog_article_ratings'))
			->select(['rating'])
			->where('article_id', '=', $articleId)
			->where('viewer_key', '=', $viewerKey);

		$row = $this->execQuery($qb, true);
		if ($row === null) {
			return null;
		}

		$rating = (int) ($row->rating ?? 0);
		if ($rating < 1 || $rating > 5) {
			return null;
		}

		return $rating;
	}

	public function saveViewerRating(
		int $articleId,
		string $viewerKey,
		int $rating,
		string $ipAddress = ''
	): bool {
		$viewerKey = trim($viewerKey);
		$ipAddress = trim($ipAddress);

		if ($articleId <= 0 || $viewerKey === '' || $rating < 1 || $rating > 5) {
			return false;
		}

		if ($ipAddress === '' || filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
			$ipAddress = '0.0.0.0';
		}

		$existing = $this->findViewerRating($articleId, $viewerKey);
		if ($existing !== null) {
			if ($existing === $rating) {
				return true;
			}

			$update = (new QueryBuilder('blog_article_ratings'))
				->update([
					'rating' => $rating,
					'ip_address' => $ipAddress,
				])
				->where('article_id', '=', $articleId)
				->where('viewer_key', '=', $viewerKey);

			return $this->execWriteQuery($update);
		}

		$insert = (new QueryBuilder('blog_article_ratings'))->insert([
			'article_id' => $articleId,
			'ip_address' => $ipAddress,
			'viewer_key' => $viewerKey,
			'rating' => $rating,
		]);

		return $this->execInsertQuery($insert) > 0;
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
			->orderBy('blog_articles.enabled', 'ASC')
			->orderBy('blog_articles.created_at', 'DESC')
			->orderBy('blog_articles.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	private function findAllWithTopicLegacy(): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*, blog_topics.title AS topic_title')
			->join('blog_topics', 'blog_articles.topic_id', 'blog_topics.id', 'LEFT')
			->orderBy('blog_articles.enabled', 'ASC')
			->orderBy('blog_articles.created_at', 'DESC')
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
			->orderBy('blog_articles.enabled', 'ASC')
			->orderBy('blog_articles.created_at', 'DESC')
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

		$qb->orderBy('blog_articles.enabled', 'ASC')
			->orderBy('blog_articles.created_at', 'DESC')
			->orderBy('blog_articles.id', 'DESC');

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

	private function findLatestViaRelations(int $limit, bool $onlyActive): array
	{
		$joinType = $onlyActive ? 'INNER' : 'LEFT';
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*, MIN(blog_topics.id) AS topic_id_resolved, MIN(blog_topics.title) AS topic_title, MIN(blog_topics.code) AS topic_code')
			->join('blog_article_topic_relations', 'blog_articles.id', 'blog_article_topic_relations.article_id', $joinType)
			->join('blog_topics', 'blog_article_topic_relations.topic_id', 'blog_topics.id', $joinType);

		if ($onlyActive) {
			$qb->where('blog_articles.enabled', '=', 1)
				->where('blog_topics.enabled', '=', 1);
		}

		$qb->groupBy('blog_articles.id')
			->orderBy('blog_articles.created_at', 'DESC')
			->orderBy('blog_articles.id', 'DESC');

		if ($limit > 0) {
			$qb->limit($limit);
		}

		return $this->execQuery($qb) ?? [];
	}

	private function findLatestLegacy(int $limit, bool $onlyActive): array
	{
		$joinType = $onlyActive ? 'INNER' : 'LEFT';
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*, blog_articles.topic_id AS topic_id_resolved, blog_topics.title AS topic_title, blog_topics.code AS topic_code')
			->join('blog_topics', 'blog_articles.topic_id', 'blog_topics.id', $joinType);

		if ($onlyActive) {
			$qb->where('blog_articles.enabled', '=', 1)
				->where('blog_topics.enabled', '=', 1);
		}

		$qb->orderBy('blog_articles.created_at', 'DESC')
			->orderBy('blog_articles.id', 'DESC');

		if ($limit > 0) {
			$qb->limit($limit);
		}

		return $this->execQuery($qb) ?? [];
	}

	private function findTopViewedTopicViaRelations(?string $sinceDatetime): ?object
	{
		$qb = (new QueryBuilder('blog_article_views'))
			->selectRaw('blog_topics.id AS id, blog_topics.title AS title, COUNT(blog_article_views.id) AS views_count')
			->join('blog_article_topic_relations', 'blog_article_views.article_id', 'blog_article_topic_relations.article_id', 'INNER')
			->join('blog_topics', 'blog_article_topic_relations.topic_id', 'blog_topics.id', 'INNER');

		$this->applyViewsSinceFilter($qb, $sinceDatetime);

		$qb->groupBy(['blog_topics.id', 'blog_topics.title'])
			->orderBy('views_count', 'DESC')
			->orderBy('blog_topics.id', 'DESC')
			->limit(1);

		$result = $this->execQuery($qb, true);
		if (!is_object($result) || (int) ($result->views_count ?? 0) <= 0) {
			return null;
		}

		return $result;
	}

	private function findTopViewedTopicLegacy(?string $sinceDatetime): ?object
	{
		$qb = (new QueryBuilder('blog_article_views'))
			->selectRaw('blog_topics.id AS id, blog_topics.title AS title, COUNT(blog_article_views.id) AS views_count')
			->join('blog_articles', 'blog_article_views.article_id', 'blog_articles.id', 'INNER')
			->join('blog_topics', 'blog_articles.topic_id', 'blog_topics.id', 'INNER');

		$this->applyViewsSinceFilter($qb, $sinceDatetime);

		$qb->groupBy(['blog_topics.id', 'blog_topics.title'])
			->orderBy('views_count', 'DESC')
			->orderBy('blog_topics.id', 'DESC')
			->limit(1);

		$result = $this->execQuery($qb, true);
		if (!is_object($result) || (int) ($result->views_count ?? 0) <= 0) {
			return null;
		}

		return $result;
	}

	private function applyViewsSinceFilter(QueryBuilder $qb, ?string $sinceDatetime): void
	{
		$sinceDatetime = trim((string) $sinceDatetime);
		if ($sinceDatetime === '') {
			return;
		}

		$qb->where('blog_article_views.created_at', '>=', $sinceDatetime);
	}

	/**
	 * @param list<object> $articles
	 * @return list<object>
	 */
	private function sortForDisplay(array $articles): array
	{
		usort($articles, static function (object $left, object $right): int {
			return self::compareForDisplay($left, $right);
		});

		return $articles;
	}

	private static function isUnpublished(object $article): bool
	{
		return (int) ($article->enabled ?? 0) !== 1;
	}

	private static function compareForDisplay(object $left, object $right): int
	{
		$leftUnpublished = self::isUnpublished($left);
		$rightUnpublished = self::isUnpublished($right);

		if ($leftUnpublished !== $rightUnpublished) {
			return $leftUnpublished ? -1 : 1;
		}

		$leftCreated = self::resolveTimestamp($left->created_at ?? null);
		$rightCreated = self::resolveTimestamp($right->created_at ?? null);

		if ($leftCreated !== $rightCreated) {
			return $rightCreated <=> $leftCreated;
		}

		return (int) ($right->id ?? 0) <=> (int) ($left->id ?? 0);
	}

	private static function resolveTimestamp(mixed $value): int
	{
		$value = trim((string) $value);
		if ($value === '') {
			return 0;
		}

		$timestamp = strtotime($value);

		return $timestamp === false ? 0 : $timestamp;
	}
}
