<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class RightsModel extends BaseModel
{
	protected string $table = 'user_rights';

	public function findAllOrdered(): array
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->orderBy('level', 'DESC')
			->orderBy('id', 'ASC');

		return $this->execQuery($qb) ?? [];
	}

	public function findDefaultId(): int
	{
		$qb = (new QueryBuilder($this->table))
			->select(['id'])
			->orderBy('level', 'ASC')
			->orderBy('id', 'ASC')
			->limit(1);

		$row = $this->execQuery($qb, true);
		return (int) ($row->id ?? 0);
	}

	public function findByRole(string $role, ?int $excludeId = null): ?object
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('role', '=', $role);

		if ($excludeId !== null && $excludeId > 0) {
			$qb->where('id', '!=', $excludeId);
		}

		return $this->execQuery($qb, true);
	}

	public function findByCode(string $code, ?int $excludeId = null): ?object
	{
		$code = strtolower(trim($code));
		if ($code === '') {
			return null;
		}

		$qb = (new QueryBuilder($this->table))
			->select()
			->where('code', '=', $code);

		if ($excludeId !== null && $excludeId > 0) {
			$qb->where('id', '!=', $excludeId);
		}

		return $this->execQuery($qb, true);
	}

	public function isCodeTaken(string $code, ?int $excludeId = null): bool
	{
		return $this->findByCode($code, $excludeId) !== null;
	}

	public function createRole(string $role, string $code, int $level): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'role' => $role,
			'code' => $code,
			'level' => $level,
		]);

		return $this->execInsertQuery($qb);
	}

	public function updateRole(int $id, string $role, string $code, int $level): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update([
				'role' => $role,
				'code' => $code,
				'level' => $level,
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
}
