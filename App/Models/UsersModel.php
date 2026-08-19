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
		$qb->select(['*', 'user_rights.level as role_level', 'user_rights.role as role_name', 'user_rights.code as role_code'])
			->join('user_rights', 'users.rights_id', 'user_rights.id', 'LEFT')
			->where('users.id', '=', $userId);

		return $this->execQuery($qb, true);
	}

	public function findByLoginWithRights(string $login): ?object
	{
		$qb = new QueryBuilder($this->table);
		$qb->select(['*', 'user_rights.level as role_level', 'user_rights.role as role_name', 'user_rights.code as role_code'])
			->join('user_rights', 'users.rights_id', 'user_rights.id', 'LEFT')
			->where('users.login', '=', $login);

		return $this->execQuery($qb, true);
	}

	public function findAllWithRights(): array
	{
		$qb = new QueryBuilder($this->table);
		$qb->select(['users.id', 'users.login', 'users.rights_id', 'user_rights.role as role_name', 'user_rights.code as role_code', 'user_rights.level as role_level'])
			->join('user_rights', 'users.rights_id', 'user_rights.id', 'LEFT')
			->orderBy('users.id', 'DESC');

		return $this->execQuery($qb) ?? [];
	}

	public function createUser(string $login, string $passwordHash, int $rightsId): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'login' => $login,
			'password' => $passwordHash,
			'rights_id' => $rightsId,
		]);

		return $this->execInsertQuery($qb);
	}

	public function deleteById(int $id): bool
	{
		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function countByRightsId(int $rightsId): int
	{
		$qb = (new QueryBuilder($this->table))
			->count()
			->where('rights_id', '=', $rightsId);

		$result = $this->execQuery($qb, true);
		if (!is_object($result)) {
			return 0;
		}

		return (int) ($result->total ?? 0);
	}

	public function countAdmins(): int
	{
		$qb = new QueryBuilder($this->table);
		$qb->count()
			->join('user_rights', 'users.rights_id', 'user_rights.id', 'LEFT')
			->whereRaw(
				'(user_rights.level >= :admin_level OR LOWER(user_rights.code) = :admin_code OR LOWER(user_rights.role) = :admin_role)',
				[
					'admin_level' => 100,
					'admin_code' => 'admin',
					'admin_role' => 'admin',
				]
			);

		$result = $this->execQuery($qb, true);
		if (!is_object($result)) {
			return 0;
		}

		return (int) ($result->total ?? 0);
	}
}
