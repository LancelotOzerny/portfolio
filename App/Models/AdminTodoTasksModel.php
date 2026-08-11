<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class AdminTodoTasksModel extends BaseModel
{
	protected string $table = 'admin_todo_tasks';

	public function findAllOrdered(): array
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->orderBy('sort_order', 'ASC')
			->orderBy('id', 'ASC');

		return $this->execQuery($qb) ?? [];
	}

	public function findByColumnId(int $columnId): array
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('column_id', '=', $columnId)
			->orderBy('sort_order', 'ASC')
			->orderBy('id', 'ASC');

		return $this->execQuery($qb) ?? [];
	}

	/**
	 * @param list<int> $dependencyIds
	 */
	public function createTask(int $columnId, string $title, string $description, array $dependencyIds = []): int
	{
		$sortOrder = $this->nextSortOrder($columnId);

		$qb = (new QueryBuilder($this->table))->insert([
			'column_id' => $columnId,
			'title' => $title,
			'description' => $description,
			'dependency_ids' => $this->encodeDependencyIds($dependencyIds),
			'sort_order' => $sortOrder,
		]);

		return $this->execInsertQuery($qb);
	}

	/**
	 * @param list<int> $dependencyIds
	 */
	public function updateTask(int $id, string $title, string $description, array $dependencyIds = [], ?int $columnId = null): bool
	{
		$data = [
			'title' => $title,
			'description' => $description,
			'dependency_ids' => $this->encodeDependencyIds($dependencyIds),
		];

		if ($columnId !== null && $columnId > 0) {
			$data['column_id'] = $columnId;
		}

		$qb = (new QueryBuilder($this->table))
			->update($data)
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function moveToColumn(int $id, int $columnId): bool
	{
		if ($id <= 0 || $columnId <= 0) {
			return false;
		}

		$sortOrder = $this->nextSortOrder($columnId);
		$qb = (new QueryBuilder($this->table))
			->update([
				'column_id' => $columnId,
				'sort_order' => $sortOrder,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function removeDependencyReferences(int $parentId): bool
	{
		if ($parentId <= 0) {
			return true;
		}

		foreach ($this->findAllOrdered() as $task) {
			$taskId = (int) ($task->id ?? 0);
			$dependencyIds = $this->decodeDependencyIds($task->dependency_ids ?? ($task->dependency_id ?? null));
			if ($dependencyIds === [] || !in_array($parentId, $dependencyIds, true)) {
				continue;
			}

			$filtered = array_values(array_filter(
				$dependencyIds,
				static fn (int $id): bool => $id !== $parentId
			));

			$qb = (new QueryBuilder($this->table))
				->update([
					'dependency_ids' => $this->encodeDependencyIds($filtered),
				])
				->where('id', '=', $taskId);

			if (!$this->execWriteQuery($qb)) {
				return false;
			}
		}

		return true;
	}

	public function deleteById(int $id): bool
	{
		$this->removeDependencyReferences($id);

		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function reorder(int $columnId, array $orderedIds): bool
	{
		$position = 1;

		foreach ($orderedIds as $taskId) {
			$taskId = (int) $taskId;
			if ($taskId <= 0) {
				continue;
			}

			$qb = (new QueryBuilder($this->table))
				->update([
					'column_id' => $columnId,
					'sort_order' => $position,
				])
				->where('id', '=', $taskId);

			if (!$this->execWriteQuery($qb)) {
				return false;
			}

			$position++;
		}

		return true;
	}

	/**
	 * @return list<int>
	 */
	public function decodeDependencyIds(mixed $raw): array
	{
		if (is_int($raw) || (is_string($raw) && ctype_digit($raw))) {
			$id = (int) $raw;
			return $id > 0 ? [$id] : [];
		}

		if (!is_string($raw)) {
			return [];
		}

		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}

		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			return [];
		}

		$result = [];
		foreach ($decoded as $value) {
			$id = (int) $value;
			if ($id > 0) {
				$result[$id] = $id;
			}
		}

		return array_values($result);
	}

	/**
	 * @param list<int> $dependencyIds
	 */
	public function encodeDependencyIds(array $dependencyIds): ?string
	{
		$normalized = [];
		foreach ($dependencyIds as $value) {
			$id = (int) $value;
			if ($id > 0) {
				$normalized[$id] = $id;
			}
		}

		if ($normalized === []) {
			return null;
		}

		return json_encode(array_values($normalized), JSON_UNESCAPED_UNICODE);
	}

	private function nextSortOrder(int $columnId): int
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('MAX(sort_order) AS max_sort')
			->where('column_id', '=', $columnId);

		$row = $this->execQuery($qb, true);
		$maxSort = (int) ($row->max_sort ?? 0);

		return $maxSort + 1;
	}
}
