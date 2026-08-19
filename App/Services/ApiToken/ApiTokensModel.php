<?php

namespace App\Services\ApiToken;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class ApiTokensModel extends BaseModel
{
	protected string $table = 'api_tokens';

	public function findActiveByUserId(int $userId): ?object
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('user_id', '=', $userId)
			->whereNull('revoked_at')
			->where('expires_at', '>', date('Y-m-d H:i:s'))
			->orderBy('id', 'DESC')
			->limit(1);

		return $this->execQuery($qb, true);
	}

	public function findActiveByHash(string $tokenHash): ?object
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('token_hash', '=', $tokenHash)
			->whereNull('revoked_at')
			->where('expires_at', '>', date('Y-m-d H:i:s'))
			->orderBy('id', 'DESC')
			->limit(1);

		return $this->execQuery($qb, true);
	}

	public function createToken(int $userId, string $tokenHash, string $tokenEncrypted, string $expiresAt): int
	{
		$qb = (new QueryBuilder($this->table))->insert([
			'user_id' => $userId,
			'token_hash' => $tokenHash,
			'token_encrypted' => $tokenEncrypted,
			'expires_at' => $expiresAt,
		]);

		return $this->execInsertQuery($qb);
	}

	public function revokeActiveByUserId(int $userId): bool
	{
		$qb = (new QueryBuilder($this->table))
			->update(['revoked_at' => date('Y-m-d H:i:s')])
			->where('user_id', '=', $userId)
			->whereNull('revoked_at');

		return $this->execWriteQuery($qb);
	}

	public function deleteByUserId(int $userId): bool
	{
		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('user_id', '=', $userId);

		return $this->execWriteQuery($qb);
	}
}
