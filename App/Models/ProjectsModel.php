<?php

namespace Models;

use Modules\Main\BaseModel;

class ProjectsModel extends BaseModel
{
	protected string $table = 'projects';

	public function findAll($limit = 0): array
	{
		$linksModel = new LinksModel();
		$projectTagsModel = new ProjectTagsModel();
		$items = parent::findAll($limit);

		foreach ($items as &$item)
		{
			$item->links = $linksModel->findAllByProject($item->id);
			$item->tags = $projectTagsModel->findAllByProjectId($item->id);
		}

		return $items;
	}
}