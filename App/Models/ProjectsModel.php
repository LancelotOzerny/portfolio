<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class ProjectsModel extends BaseModel
{
	protected string $table = 'projects';

	public function createForAdmin(string $name, int $active = 0): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'name' => $name,
			'active' => $active,
			'preview_text' => '',
			'detail_text' => '',
			'preview_image_url' => '',
			'detail_image_url' => '',
		]);

		return $this->execInsertQuery($qb);
	}

	public function updateMainInfo(int $id, string $name, int $active): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update([
				'name' => $name,
				'active' => $active,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function updateEditorData(
		int $id,
		string $name,
		int $active,
		string $previewText,
		string $detailText,
		string $previewImageUrl,
		string $detailImageUrl
	): bool {
		$qb = (new QueryBuilder($this->table))
			->update([
				'name' => $name,
				'active' => $active,
				'preview_text' => $previewText,
				'detail_text' => $detailText,
				'preview_image_url' => $previewImageUrl,
				'detail_image_url' => $detailImageUrl,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function findAllBase(int $limit = 0): array
	{
		$qb = new QueryBuilder($this->table);
		$qb->select();
		$qb->orderBy("id", "DESC");

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
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('active', '=', 1)
			->orderBy('id', 'DESC');

		if ($limit > 0)
		{
			$qb->limit($limit);
		}

		$items = $this->execQuery($qb) ?? [];

		foreach ($items as $item)
		{
			$item->links = $linksModel->findAllByProject($item->id);
			$item->tags = $projectTagsModel->findAllByProjectId($item->id);
		}

		return $items;
	}

	public function findById(int $id): ?object
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('id', '=', $id)
			->where('active', '=', 1);
		$project = $this->execQuery($qb, true);

		if ($project)
		{
			$project->links = (new LinksModel())->findAllByProject($project->id);
			$project->tags = (new ProjectTagsModel())->findAllByProjectId($project->id);
			$project->info = (new ProjectsInfoModel())->findAllByProjectId($id);
		}

		return $project;
	}
}
