<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class WorkExperienceModel extends BaseModel
{
	protected string $table = 'work_experience';

	public function findAllOrdered(): array
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->orderBy('is_current', 'DESC')
			->orderBy('end_date', 'DESC')
			->orderBy('start_date', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	public function findAllActiveOrdered(): array
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('active', '=', 1)
			->orderBy('is_current', 'DESC')
			->orderBy('end_date', 'DESC')
			->orderBy('start_date', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	public function create(
		string $position,
		string $company,
		string $startDate,
		?string $endDate,
		bool $isCurrent,
		string $description,
		bool $active
	): int {
		$qb = (new QueryBuilder($this->table))->insert([
			'position' => $position,
			'company' => $company,
			'start_date' => $startDate,
			'end_date' => $endDate,
			'is_current' => $isCurrent ? 1 : 0,
			'description' => $description,
			'active' => $active ? 1 : 0,
		]);

		return $this->execInsertQuery($qb);
	}

	public function updateExperience(
		int $id,
		string $position,
		string $company,
		string $startDate,
		?string $endDate,
		bool $isCurrent,
		string $description,
		bool $active
	): bool {
		$qb = (new QueryBuilder($this->table))
			->update([
				'position' => $position,
				'company' => $company,
				'start_date' => $startDate,
				'end_date' => $endDate,
				'is_current' => $isCurrent ? 1 : 0,
				'description' => $description,
				'active' => $active ? 1 : 0,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}
}
