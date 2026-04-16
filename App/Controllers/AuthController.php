<?php

namespace Controllers;

use Models\UsersModel;
use Modules\Main\Auth;
use Modules\Validator\AuthValidator;
use Throwable;

class AuthController
{
	public function login(): void
	{
		header('Content-Type: application/json; charset=utf-8');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: POST, OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type');

		if (strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
			http_response_code(204);
			return;
		}

		$payload = $this->getPayload();
		$validation = (new AuthValidator())->validateLoginPayload($payload);

		if ($validation['is_valid'] !== true) {
			$this->jsonResponse([
				'success' => false,
				'message' => 'Ошибка валидации',
				'errors' => $validation['errors'],
			], 422);
			return;
		}

		$login = $validation['data']['login'];
		$password = $validation['data']['password'];

		try {
			$user = (new UsersModel())->findByLoginWithRights($login);
		} catch (Throwable) {
			$this->jsonResponse([
				'success' => false,
				'message' => 'Ошибка сервиса авторизации',
			], 500);
			return;
		}

		if ($user === null) {
			$this->jsonResponse([
				'success' => false,
				'message' => 'Неверный логин или пароль',
			], 401);
			return;
		}

		$storedPassword = $this->getStoredPassword($user);
		if ($storedPassword === null || !$this->verifyPassword($password, $storedPassword)) {
			$this->jsonResponse([
				'success' => false,
				'message' => 'Неверный логин или пароль',
			], 401);
			return;
		}

		$auth = Auth::getInstance();
		if (!$auth->loginById((int) $user->id)) {
			$this->jsonResponse([
				'success' => false,
				'message' => 'Не удалось авторизовать пользователя',
			], 500);
			return;
		}

		$currentUser = $auth->getCurrentUserData();

		$this->jsonResponse([
			'success' => true,
			'message' => 'Авторизация выполнена успешно',
			'redirect' => '/admin/',
			'user' => [
				'id' => (int) ($currentUser->id ?? 0),
				'login' => (string) ($currentUser->login ?? ''),
				'email' => (string) ($currentUser->email ?? ''),
				'role' => (string) ($currentUser->role_name ?? ''),
				'role_level' => (int) ($currentUser->role_level ?? 0),
			],
		]);
	}

	private function getPayload(): array
	{
		$raw = file_get_contents('php://input');
		if (is_string($raw) && trim($raw) !== '') {
			$json = json_decode($raw, true);
			if (is_array($json)) {
				return $json;
			}
		}

		return is_array($_POST) ? $_POST : [];
	}

	private function getStoredPassword(object $user): ?string
	{
		$candidates = ['password_hash', 'password', 'pass'];

		foreach ($candidates as $field) {
			if (isset($user->{$field}) && is_string($user->{$field}) && $user->{$field} !== '') {
				return $user->{$field};
			}
		}

		return null;
	}

	private function verifyPassword(string $password, string $storedPassword): bool
	{
		if (str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$argon2')) {
			return password_verify($password, $storedPassword);
		}

		if (strlen($storedPassword) === 32 && ctype_xdigit($storedPassword)) {
			return hash_equals(strtolower($storedPassword), md5($password));
		}

		if (strlen($storedPassword) === 40 && ctype_xdigit($storedPassword)) {
			return hash_equals(strtolower($storedPassword), sha1($password));
		}

		return hash_equals($storedPassword, $password);
	}

	private function jsonResponse(array $payload, int $statusCode = 200): void
	{
		http_response_code($statusCode);
		echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	}
}
