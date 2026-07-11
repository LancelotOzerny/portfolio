<?php

namespace Modules\Main;

use Models\UsersModel;
use Throwable;

class Auth
{
	private const SESSION_USER_ID_KEY = 'auth_user_id';
	private static ?self $instance = null;
	private ?UsersModel $usersModel = null;

	private function __construct()
	{
		$this->startSession();
	}

	private function __clone() {}

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getCurrentUser(): ?object
	{
		$userId = $this->getCurrentUserId();

		if ($userId <= 0) {
			return null;
		}

		try {
			return $this->getUsersModel()->findById($userId);
		} catch (Throwable) {
			return null;
		}
	}

	public function loginById(int $userId): bool
	{
		if ($userId <= 0) {
			return false;
		}

		try {
			$user = $this->getUsersModel()->findById($userId);
		} catch (Throwable) {
			return false;
		}

		if ($user === null) {
			return false;
		}

		session_regenerate_id(true);
		$_SESSION[self::SESSION_USER_ID_KEY] = (int) $user->id;
		return true;
	}

	public function logout(): void
	{
		unset($_SESSION[self::SESSION_USER_ID_KEY]);
		session_regenerate_id(true);
	}

	public function isAdmin(): bool
	{
		$user = $this->getCurrentUserData();
		if ($user === null) {
			return false;
		}

		$roleLevel = (int) ($user->role_level ?? 0);
		$roleName = strtolower((string) ($user->role_name ?? ''));

		return $roleLevel >= 100 || $roleName === 'admin';
	}

	public function getCurrentUserData(): ?object
	{
		$userId = $this->getCurrentUserId();

		if ($userId <= 0) {
			return null;
		}

		try {
			return $this->getUsersModel()->findWithRights($userId);
		} catch (Throwable) {
			return null;
		}
	}

	private function getCurrentUserId(): int
	{
		return (int) ($_SESSION[self::SESSION_USER_ID_KEY] ?? 0);
	}

	private function getUsersModel(): UsersModel
	{
		if ($this->usersModel === null) {
			$this->usersModel = new UsersModel();
		}

		return $this->usersModel;
	}

	private function startSession(): void
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}
}
