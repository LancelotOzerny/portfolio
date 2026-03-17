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
}