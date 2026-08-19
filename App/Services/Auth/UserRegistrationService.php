<?php

namespace App\Services\Auth;

use InvalidArgumentException;
use Models\RightsModel;
use Models\UsersModel;
use RuntimeException;

class UserRegistrationService
{
	public function __construct(
		private ?UsersModel $usersModel = null,
		private ?RightsModel $rightsModel = null,
	) {
		$this->usersModel = $usersModel ?? new UsersModel();
		$this->rightsModel = $rightsModel ?? new RightsModel();
	}

	public function register(string $login, string $password, ?int $rightsId = null): object
	{
		if ($this->usersModel->findByLogin($login) !== null) {
			throw new InvalidArgumentException('Пользователь с таким логином уже существует.');
		}

		$resolvedRightsId = $rightsId !== null && $rightsId > 0
			? $rightsId
			: $this->rightsModel->findDefaultId();

		if ($resolvedRightsId <= 0 || $this->rightsModel->findById($resolvedRightsId) === null) {
			throw new InvalidArgumentException('Указанная роль не найдена.');
		}

		$userId = $this->usersModel->createUser(
			$login,
			password_hash($password, PASSWORD_DEFAULT),
			$resolvedRightsId
		);

		if ($userId <= 0) {
			throw new RuntimeException('Не удалось создать пользователя.');
		}

		$user = $this->usersModel->findWithRights($userId);
		if ($user === null) {
			throw new RuntimeException('Не удалось загрузить созданного пользователя.');
		}

		return $user;
	}
}
