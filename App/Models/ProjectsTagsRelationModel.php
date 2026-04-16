<?php

namespace Models;

use Modules\Main\BaseModel;

class ProjectsTagsRelationModel extends BaseModel
{
	protected string $table = 'proejcts_tags';

	public function findAllByProjectId(int $projectId): array
	{
		return $this->findAllBy('project_id', $projectId);
	}
}
