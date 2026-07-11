<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class TagsModel extends BaseModel
{
	protected string $table = 'tags';

	public function findAllOrdered(): array
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->orderBy('name', 'ASC');

		return $this->execQuery($qb) ?? [];
	}

	public function findByName(string $name, ?int $excludeId = null): ?object
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('name', '=', $name);

		if ($excludeId !== null && $excludeId > 0) {
			$qb->where('id', '!=', $excludeId);
		}

		return $this->execQuery($qb, true);
	}

	public function create(string $name, bool $useAsFilter): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'name' => $name,
			'use_as_filter' => $useAsFilter ? 1 : 0,
		]);

		return $this->execInsertQuery($qb);
	}

	public function updateTag(int $id, string $name, bool $useAsFilter): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update([
				'name' => $name,
				'use_as_filter' => $useAsFilter ? 1 : 0,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function deleteTag(int $id): bool
	{
		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}
}
