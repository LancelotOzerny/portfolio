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

	public function createTask(int $columnId, string $title, string $description): int
	{
		$sortOrder = $this->nextSortOrder($columnId);

		$qb = (new QueryBuilder($this->table))->insert([
			'column_id' => $columnId,
			'title' => $title,
			'description' => $description,
			'sort_order' => $sortOrder,
		]);

		return $this->execInsertQuery($qb);
	}

	public function updateTask(int $id, string $title, string $description): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update([
				'title' => $title,
				'description' => $description,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function deleteById(int $id): bool
	{
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
