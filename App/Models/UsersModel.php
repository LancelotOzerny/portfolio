<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class UsersModel extends BaseModel
{
	protected string $table = 'users';

	public function findByLogin(string $login): ?object
	{
		return $this->findBy('login', $login);
	}

	public function findByEmail(string $email): ?object
	{
		return $this->findBy('email', $email);
	}

	public function findWithRights(int $userId): ?object
	{
		$qb = new QueryBuilder($this->table);
		$qb->select(['*', 'user_rights.level as role_level', 'user_rights.role as role_name'])
			->join('user_rights', 'users.rights_id', 'user_rights.id', 'LEFT')
			->where('users.id', '=', $userId);

		return $this->execQuery($qb, true);
	}

	public function findByLoginWithRights(string $login): ?object
	{
		$qb = new QueryBuilder($this->table);
		$qb->select(['*', 'user_rights.level as role_level', 'user_rights.role as role_name'])
			->join('user_rights', 'users.rights_id', 'user_rights.id', 'LEFT')
			->where('users.login', '=', $login);

		return $this->execQuery($qb, true);
	}

	public function findAllWithRights(): array
	{
		$qb = new QueryBuilder($this->table);
		$qb->select(['users.id', 'users.login', 'user_rights.role as role_name', 'user_rights.level as role_level'])
			->join('user_rights', 'users.rights_id', 'user_rights.id', 'LEFT')
			->orderBy('users.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}
}
