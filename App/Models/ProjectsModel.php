<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class ProjectsModel extends BaseModel
{
	protected string $table = 'projects';

	public function createForAdmin(string $name, int $active = 0): int
	{
		$sql = "INSERT INTO {$this->table} (name, active, preview_text, detail_text, preview_image_url, detail_image_url)
			VALUES (:name, :active, :preview_text, :detail_text, :preview_image_url, :detail_image_url)";
		$stmt = $this->db->prepare($sql);

		$ok = $stmt->execute([
			':name' => $name,
			':active' => $active,
			':preview_text' => '',
			':detail_text' => '',
			':preview_image_url' => '',
			':detail_image_url' => '',
		]);

		if (!$ok) {
			return 0;
		}

		return (int) $this->db->lastInsertId();
	}

	public function updateMainInfo(int $id, string $name, int $active): bool
	{
		$sql = "UPDATE {$this->table} SET name = :name, active = :active WHERE id = :id";
		$stmt = $this->db->prepare($sql);

		return $stmt->execute([
			':name' => $name,
			':active' => $active,
			':id' => $id,
		]);
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
		$sql = "UPDATE {$this->table}
			SET name = :name,
				active = :active,
				preview_text = :preview_text,
				detail_text = :detail_text,
				preview_image_url = :preview_image_url,
				detail_image_url = :detail_image_url
			WHERE id = :id";
		$stmt = $this->db->prepare($sql);

		return $stmt->execute([
			':name' => $name,
			':active' => $active,
			':preview_text' => $previewText,
			':detail_text' => $detailText,
			':preview_image_url' => $previewImageUrl,
			':detail_image_url' => $detailImageUrl,
			':id' => $id,
		]);
	}

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
