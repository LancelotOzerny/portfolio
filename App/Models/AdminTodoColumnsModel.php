<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class AdminTodoColumnsModel extends BaseModel
{
	protected string $table = 'admin_todo_columns';

	public function findAllOrdered(): array
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->orderBy('sort_order', 'ASC')
			->orderBy('id', 'ASC');

		return $this->execQuery($qb) ?? [];
	}

	public function updateColor(int $id, string $color): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update(['color' => $color])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function ensureDefaults(): void
	{
		if ($this->findAllOrdered() !== []) {
			return;
		}

		$defaults = [
			['code' => 'planned', 'title' => 'Планируется', 'color' => '#6c757d', 'sort_order' => 1],
			['code' => 'in_progress', 'title' => 'В работе', 'color' => '#0d6efd', 'sort_order' => 2],
			['code' => 'done', 'title' => 'Готово', 'color' => '#198754', 'sort_order' => 3],
		];

		foreach ($defaults as $column) {
			$qb = (new QueryBuilder($this->table))->insert($column);
			$this->execInsertQuery($qb);
		}
	}
}
