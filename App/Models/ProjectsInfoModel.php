<?php

namespace Models;

use Modules\Main\BaseModel;

class ProjectsInfoModel extends BaseModel
{
	protected string $table = 'projects_info';

	public function findAllByProjectId(int $projectId): array
	{
		return $this->findAllBy('project_id', $projectId);
	}
}
