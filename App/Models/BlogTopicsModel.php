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
			'image_path' => '',
			'enabled' => 0,
		]);

		return $this->execInsertQuery($qb);
	}

	public function updateEditorData(int $id, string $title, string $description, string $imagePath, int $enabled): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update([
				'title' => $title,
				'preview_text' => $description,
				'image_path' => $imagePath,
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
			->selectRaw('blog_topics.*, COUNT(blog_articles.id) AS articles_count')
			->join('blog_articles', 'blog_topics.id', 'blog_articles.topic_id', 'LEFT');

		if ($onlyEnabled) {
			$qb->where('blog_topics.enabled', '=', 1);
		}

		$qb
			->groupBy('blog_topics.id')
			->orderBy('blog_topics.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}
}
