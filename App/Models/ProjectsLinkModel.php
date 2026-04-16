<?php

namespace Models;

use Modules\Main\BaseModel;

class ProjectsLinkModel extends BaseModel
{
	protected string $table = 'projects_link';

	public function findAllByProjectId(int $projectId): array
	{
		return $this->findAllBy('project_id', $projectId);
	}
}
