<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class UsersModel extends BaseModel
{
	protected string $table = 'users';

	public function findByEmail(string $email): ?object
	{
		return $this->findBy('email', $email);
	}

	public function findWithRights(int $userId): ?object
	{
		$qb = new QueryBuilder($this->table);
		$qb->select(['*', 'rights.level as role_level', 'rights.name as role_name'])
			->join('rights', 'rights_id', 'rights.id', 'RIGHT')
			->where('users.id', '=', $userId);

		return $this->execQuery($qb, true);
	}
}