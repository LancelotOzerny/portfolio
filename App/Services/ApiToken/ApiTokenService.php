<?php

namespace App\Services\ApiToken;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Models\UsersModel;
use RuntimeException;

final class ApiTokenService
{
	private const TTL = 'P1M';

	public function __construct(
		private readonly ApiTokensModel $tokensModel = new ApiTokensModel(),
		private readonly TokenHasher $hasher = new TokenHasher(),
		private readonly TokenCipher $cipher = new TokenCipher(),
		private readonly UsersModel $usersModel = new UsersModel(),
	) {
	}

	public function findActive(int $userId): ?object
	{
		if ($userId <= 0) {
			return null;
		}

		return $this->tokensModel->findActiveByUserId($userId);
	}

	public function hasActive(int $userId): bool
	{
		return $this->findActive($userId) !== null;
	}

	public function issue(int $userId): IssuedToken
	{
		if ($userId <= 0 || $this->usersModel->findById($userId) === null) {
			throw new InvalidArgumentException('Пользователь не найден.');
		}

		$this->tokensModel->revokeActiveByUserId($userId);

		$plainToken = bin2hex(random_bytes(32));
		$expiresAt = (new DateTimeImmutable('now'))->add(new DateInterval(self::TTL))->format('Y-m-d H:i:s');
		$tokenId = $this->tokensModel->createToken(
			$userId,
			$this->hasher->hash($plainToken),
			$this->cipher->encrypt($plainToken),
			$expiresAt
		);

		if ($tokenId <= 0) {
			throw new RuntimeException('Не удалось создать токен.');
		}

		return new IssuedToken($plainToken, $expiresAt);
	}

	public function regenerate(int $userId): IssuedToken
	{
		return $this->issue($userId);
	}

	public function reveal(int $userId): string
	{
		$record = $this->findActive($userId);
		if ($record === null) {
			throw new InvalidArgumentException('У пользователя нет действующего токена.');
		}

		return $this->cipher->decrypt((string) ($record->token_encrypted ?? ''));
	}

	public function findUserByToken(string $plainToken): ?object
	{
		$plainToken = trim($plainToken);
		if ($plainToken === '') {
			return null;
		}

		$record = $this->tokensModel->findActiveByHash($this->hasher->hash($plainToken));
		if ($record === null) {
			return null;
		}

		$userId = (int) ($record->user_id ?? 0);
		if ($userId <= 0) {
			return null;
		}

		return $this->usersModel->findWithRights($userId);
	}

	public function revoke(int $userId): void
	{
		if ($userId > 0) {
			$this->tokensModel->revokeActiveByUserId($userId);
		}
	}
}
