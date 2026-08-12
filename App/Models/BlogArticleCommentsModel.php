<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;
use Throwable;

class BlogArticleCommentsModel extends BaseModel
{
	protected string $table = 'blog_article_comments';

	/**
	 * @return list<object>
	 */
	public function findAllForAdmin(): array
	{
		try {
			return $this->findAllForAdminViaRelations();
		} catch (Throwable) {
			return $this->findAllForAdminLegacy();
		}
	}

	public function findByArticleId(int $articleId): array
	{
		if ($articleId <= 0) {
			return [];
		}

		$qb = (new QueryBuilder($this->table))
			->select()
			->where('article_id', '=', $articleId)
			->orderBy('created_at', 'ASC')
			->orderBy('id', 'ASC');

		return $this->execQuery($qb) ?? [];
	}

	public function countByArticleId(int $articleId): int
	{
		if ($articleId <= 0) {
			return 0;
		}

		try {
			$qb = (new QueryBuilder($this->table))
				->count()
				->where('article_id', '=', $articleId);

			$result = $this->execQuery($qb, true);
			if (!is_object($result)) {
				return 0;
			}

			return (int) ($result->total ?? 0);
		} catch (Throwable) {
			return 0;
		}
	}

	public function createComment(
		int $articleId,
		string $authorName,
		string $commentText,
		?int $parentId = null,
		string $viewerKey = ''
	): int {
		if ($articleId <= 0 || trim($commentText) === '') {
			return 0;
		}

		$data = [
			'article_id' => $articleId,
			'updated_by_name' => mb_substr(trim($authorName), 0, 255),
			'comment_text' => trim($commentText),
		];

		if ($parentId !== null && $parentId > 0) {
			$data['parent_id'] = $parentId;
		}

		$viewerKey = trim($viewerKey);
		if ($viewerKey !== '') {
			$data['viewer_key'] = $viewerKey;
		}

		return $this->execInsertQuery((new QueryBuilder($this->table))->insert($data));
	}

	public function countSince(string $sinceDatetime): int
	{
		$sinceDatetime = trim($sinceDatetime);
		if ($sinceDatetime === '') {
			return 0;
		}

		try {
			$qb = (new QueryBuilder($this->table))
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

	public function belongsToArticle(int $commentId, int $articleId): bool
	{
		if ($commentId <= 0 || $articleId <= 0) {
			return false;
		}

		$qb = (new QueryBuilder($this->table))
			->select(['id'])
			->where('id', '=', $commentId)
			->where('article_id', '=', $articleId);

		return $this->execQuery($qb, true) !== null;
	}

	/**
	 * @param list<int> $commentIds
	 * @return array<int, array{likes: int, dislikes: int}>
	 */
	public function getVoteSummariesByCommentIds(array $commentIds): array
	{
		$ids = [];
		foreach ($commentIds as $commentId) {
			$commentId = (int) $commentId;
			if ($commentId > 0) {
				$ids[$commentId] = $commentId;
			}
		}

		if ($ids === []) {
			return [];
		}

		$qb = (new QueryBuilder('blog_article_comment_votes'))
			->selectRaw(
				'comment_id,
				SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END) AS likes_count,
				SUM(CASE WHEN vote = -1 THEN 1 ELSE 0 END) AS dislikes_count'
			)
			->whereIn('comment_id', array_values($ids))
			->groupBy('comment_id');

		$rows = $this->execQuery($qb) ?? [];
		$result = [];

		foreach ($rows as $row) {
			$commentId = (int) ($row->comment_id ?? 0);
			if ($commentId <= 0) {
				continue;
			}

			$result[$commentId] = [
				'likes' => (int) ($row->likes_count ?? 0),
				'dislikes' => (int) ($row->dislikes_count ?? 0),
			];
		}

		return $result;
	}

	/**
	 * @param list<int> $commentIds
	 * @return array<int, int>
	 */
	public function findViewerVotesByCommentIds(array $commentIds, string $viewerKey): array
	{
		$viewerKey = trim($viewerKey);
		$ids = [];
		foreach ($commentIds as $commentId) {
			$commentId = (int) $commentId;
			if ($commentId > 0) {
				$ids[$commentId] = $commentId;
			}
		}

		if ($viewerKey === '' || $ids === []) {
			return [];
		}

		$qb = (new QueryBuilder('blog_article_comment_votes'))
			->select(['comment_id', 'vote'])
			->where('viewer_key', '=', $viewerKey)
			->whereIn('comment_id', array_values($ids));

		$rows = $this->execQuery($qb) ?? [];
		$result = [];

		foreach ($rows as $row) {
			$commentId = (int) ($row->comment_id ?? 0);
			$vote = (int) ($row->vote ?? 0);
			if ($commentId <= 0 || ($vote !== 1 && $vote !== -1)) {
				continue;
			}

			$result[$commentId] = $vote;
		}

		return $result;
	}

	public function findViewerVote(int $commentId, string $viewerKey): ?int
	{
		$viewerKey = trim($viewerKey);
		if ($commentId <= 0 || $viewerKey === '') {
			return null;
		}

		$qb = (new QueryBuilder('blog_article_comment_votes'))
			->select(['vote'])
			->where('comment_id', '=', $commentId)
			->where('viewer_key', '=', $viewerKey);

		$row = $this->execQuery($qb, true);
		if ($row === null) {
			return null;
		}

		$vote = (int) ($row->vote ?? 0);
		if ($vote !== 1 && $vote !== -1) {
			return null;
		}

		return $vote;
	}

	public function saveViewerVote(int $commentId, string $viewerKey, int $vote): bool
	{
		$viewerKey = trim($viewerKey);
		if ($commentId <= 0 || $viewerKey === '' || ($vote !== 1 && $vote !== -1)) {
			return false;
		}

		$existing = $this->findViewerVote($commentId, $viewerKey);
		if ($existing === $vote) {
			$delete = (new QueryBuilder('blog_article_comment_votes'))
				->delete()
				->where('comment_id', '=', $commentId)
				->where('viewer_key', '=', $viewerKey);

			return $this->execWriteQuery($delete);
		}

		if ($existing !== null) {
			$update = (new QueryBuilder('blog_article_comment_votes'))
				->update(['vote' => $vote])
				->where('comment_id', '=', $commentId)
				->where('viewer_key', '=', $viewerKey);

			return $this->execWriteQuery($update);
		}

		$insert = (new QueryBuilder('blog_article_comment_votes'))->insert([
			'comment_id' => $commentId,
			'viewer_key' => $viewerKey,
			'vote' => $vote,
		]);

		return $this->execInsertQuery($insert) > 0;
	}

	/**
	 * @return list<object>
	 */
	private function findAllForAdminViaRelations(): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw(
				'blog_article_comments.id,
				blog_article_comments.updated_by_name,
				blog_article_comments.comment_text,
				blog_article_comments.article_id,
				MIN(blog_articles.code) AS article_code,
				MIN(blog_topics.id) AS topic_id,
				MIN(blog_topics.code) AS topic_code'
			)
			->join('blog_articles', 'blog_article_comments.article_id', 'blog_articles.id', 'LEFT')
			->join('blog_article_topic_relations', 'blog_articles.id', 'blog_article_topic_relations.article_id', 'LEFT')
			->join('blog_topics', 'blog_article_topic_relations.topic_id', 'blog_topics.id', 'LEFT')
			->groupBy('blog_article_comments.id')
			->orderBy('blog_article_comments.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	/**
	 * @return list<object>
	 */
	private function findAllForAdminLegacy(): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw(
				'blog_article_comments.id,
				blog_article_comments.updated_by_name,
				blog_article_comments.comment_text,
				blog_article_comments.article_id,
				blog_articles.code AS article_code,
				blog_topics.id AS topic_id,
				blog_topics.code AS topic_code'
			)
			->join('blog_articles', 'blog_article_comments.article_id', 'blog_articles.id', 'LEFT')
			->join('blog_topics', 'blog_articles.topic_id', 'blog_topics.id', 'LEFT')
			->orderBy('blog_article_comments.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}
}
