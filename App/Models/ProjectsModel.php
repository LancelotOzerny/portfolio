<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class ProjectsModel extends BaseModel
{
	protected string $table = 'projects';

	public function findAllBase(int $limit = 0): array
	{
		$qb = new QueryBuilder($this->table);
		$qb->select();

		if ($limit > 0) {
			$qb->limit($limit);
		}

		return $this->execQuery($qb) ?? [];
	}

	public function findBaseById(int $id): ?object
	{
		$qb = new QueryBuilder($this->table);
		$qb->select()->where('id', '=', $id);

		return $this->execQuery($qb, true);
	}

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

	public function findById(int $id): ?object
	{
		$linksModel = new LinksModel();
		$projectTagsModel = new ProjectTagsModel();
		$project = parent::findById($id);

		if ($project)
		{
			$project->links = $linksModel->findAllByProject($project->id);
			$project->tags = $projectTagsModel->findAllByProjectId($project->id);
		}

		return $project;
	}
}
