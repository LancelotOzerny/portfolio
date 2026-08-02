<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class BlogTopicsModel extends BaseModel
{
	protected string $table = 'blog_topics';

	public function createForAdmin(string $title): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'title' => $title,
			'preview_text' => '',
			'detail_text' => '',
			'detail_image_path' => '',
			'image_path' => '',
			'enabled' => 0,
		]);

		return $this->execInsertQuery($qb);
	}

	public function updateEditorData(
		int $id,
		string $title,
		string $description,
		string $imagePath,
		string $detailText,
		string $detailImagePath,
		int $enabled
	): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update([
				'title' => $title,
				'preview_text' => $description,
				'image_path' => $imagePath,
				'detail_text' => $detailText,
				'detail_image_path' => $detailImagePath,
				'enabled' => $enabled,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function deleteById(int $id): bool
	{
		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function findAllWithArticleCounts(bool $onlyEnabled = true): array
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('blog_topics.*, COUNT(DISTINCT CASE WHEN blog_articles.enabled = 1 THEN blog_articles.id END) AS articles_count')
			->join('blog_article_topic_relations', 'blog_topics.id', 'blog_article_topic_relations.topic_id', 'LEFT')
			->join('blog_articles', 'blog_article_topic_relations.article_id', 'blog_articles.id', 'LEFT');

		if ($onlyEnabled) {
			$qb->where('blog_topics.enabled', '=', 1);
		}

		$qb->groupBy('blog_topics.id');

		if ($onlyEnabled) {
			$qb->havingRaw('articles_count > 0');
		}

		$qb->orderBy('blog_topics.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}
}
