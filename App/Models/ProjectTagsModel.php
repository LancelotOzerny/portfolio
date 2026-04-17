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
		$deleteStmt = $this->db->prepare("DELETE FROM {$this->table} WHERE project_id = :project_id");
		if (!$deleteStmt->execute([':project_id' => $projectId])) {
			throw new \RuntimeException('Unable to clear project tags.');
		}

		if (empty($tagIds)) {
			return true;
		}

		$insertStmt = $this->db->prepare("INSERT INTO {$this->table} (project_id, tag_id) VALUES (:project_id, :tag_id)");

		foreach ($tagIds as $tagId) {
			$normalizedTagId = (int) $tagId;
			if ($normalizedTagId <= 0) {
				continue;
			}

			if (!$insertStmt->execute([
				':project_id' => $projectId,
				':tag_id' => $normalizedTagId,
			])) {
				throw new \RuntimeException('Unable to save project tags.');
			}
		}

		return true;
	}
}
