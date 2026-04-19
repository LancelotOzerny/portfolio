<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class ProjectsTagsRelationModel extends BaseModel
{
	protected string $table = 'proejcts_tags';

	public function findAllByProjectId(int $projectId): array
	{
		return $this->findAllBy('project_id', $projectId);
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
}
