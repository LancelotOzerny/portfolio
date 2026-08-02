<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class BlogArticlesModel extends BaseModel
{
	protected string $table = 'blog_articles';

	public function createForAdmin(int $topicId, string $title): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'topic_id' => $topicId,
			'title' => $title,
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
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_articles.*, GROUP_CONCAT(DISTINCT blog_topics.title ORDER BY blog_topics.title SEPARATOR ", ") AS topic_title')
			->join('blog_article_topic_relations', 'blog_articles.id', 'blog_article_topic_relations.article_id', 'LEFT')
			->join('blog_topics', 'blog_article_topic_relations.topic_id', 'blog_topics.id', 'LEFT')
			->groupBy('blog_articles.id')
			->orderBy('blog_articles.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	public function findTopicIdsByArticleId(int $articleId): array
	{
		$qb = (new QueryBuilder('blog_article_topic_relations'))
			->select(['topic_id'])
			->where('article_id', '=', $articleId)
			->orderBy('topic_id', 'ASC');

		$items = $this->execQuery($qb) ?? [];
		$result = [];

		foreach ($items as $item) {
			$topicId = (int) ($item->topic_id ?? 0);
			if ($topicId > 0) {
				$result[] = $topicId;
			}
		}

		return array_values(array_unique($result));
	}

	public function replaceTopicIds(int $articleId, array $topicIds): bool
	{
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
	}
}
