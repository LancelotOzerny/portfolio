<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class ProjectTagsModel extends BaseModel
{
	protected string $table = 'projects_tags';

	public function findAllByProjectId(int $project_id): array
	{
		$qb = (new QueryBuilder($this->table))
			->select(['tags.*'])
			->join('tags', 'tags.id', 'tag_id', 'RIGHT')
			->where('project_id', '=', $project_id);

		return $this->execQuery($qb) ?? [];
	}

	public function replaceProjectTags(int $projectId, array $tagIds): bool
	{
		$deleteQb = (new QueryBuilder($this->table))
			->delete()
			->where('project_id', '=', $projectId);

		if (!$this->execWriteQuery($deleteQb)) {
			throw new \RuntimeException('Unable to clear project tags.');
		}

		if (empty($tagIds)) {
			return true;
		}

		foreach ($tagIds as $tagId) {
			$normalizedTagId = (int) $tagId;
			if ($normalizedTagId <= 0) {
				continue;
			}

			$insertQb = (new QueryBuilder($this->table))->insert([
				'project_id' => $projectId,
				'tag_id' => $normalizedTagId,
			]);

			if (!$this->execWriteQuery($insertQb)) {
				throw new \RuntimeException('Unable to save project tags.');
			}
		}

		return true;
	}

	public function deleteByTagId(int $tagId): bool
	{
		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('tag_id', '=', $tagId);

		return $this->execWriteQuery($qb);
	}

	public function countByTagId(int $tagId): int
	{
		$qb = (new QueryBuilder($this->table))
			->count()
			->where('tag_id', '=', $tagId);

		$result = $this->execQuery($qb, true);
		if (!is_object($result)) {
			return 0;
		}

		return (int) ($result->total ?? 0);
	}
}
