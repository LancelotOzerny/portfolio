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
		string $detailImageUrl,
		?int $categoryId,
		string $tags
	): bool {
		$qb = (new QueryBuilder($this->table))
			->update([
				'name' => $name,
				'active' => $active,
				'preview_text' => $previewText,
				'detail_text' => $detailText,
				'preview_image_url' => $previewImageUrl,
				'detail_image_url' => $detailImageUrl,
				'category_id' => $categoryId,
				'tags' => $tags,
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
		$categoriesById = $this->getCategoriesById();
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
			$this->hydrateCategoryAndTags($item, $categoriesById);
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
			$this->hydrateCategoryAndTags($project, $this->getCategoriesById());
			$project->info = (new ProjectsInfoModel())->findAllByProjectId($id);
		}

		return $project;
	}

	public function countByCategoryId(int $categoryId): int
	{
		$qb = (new QueryBuilder($this->table))
			->count()
			->where('category_id', '=', $categoryId);
		$result = $this->execQuery($qb, true);

		return (int) ($result->total ?? 0);
	}

	public function clearCategoryId(int $categoryId): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update(['category_id' => null])
			->where('category_id', '=', $categoryId);

		return $this->execWriteQuery($qb);
	}

	private function getCategoriesById(): array
	{
		$categories = [];
		foreach ((new TagsModel())->findAllOrdered() as $category) {
			$categoryId = (int) ($category->id ?? 0);
			if ($categoryId > 0) {
				$categories[$categoryId] = $category;
			}
		}

		return $categories;
	}

	private function hydrateCategoryAndTags(object $project, array $categoriesById): void
	{
		$categoryId = (int) ($project->category_id ?? 0);
		$project->category = $categoriesById[$categoryId] ?? null;
		$project->tags = $this->parseTags((string) ($project->tags ?? ''));
	}

	private function parseTags(string $rawTags): array
	{
		$tags = [];
		foreach (explode(',', $rawTags) as $tag) {
			$tag = trim($tag);
			if ($tag !== '' && !in_array($tag, $tags, true)) {
				$tags[] = $tag;
			}
		}

		return $tags;
	}
}
