<?php

namespace Modules\Validator;

class AuthValidator
{
	public function validateLoginPayload(array $payload): array
	{
		$errors = [];

		$login = trim((string) ($payload['login'] ?? ''));
		$password = (string) ($payload['password'] ?? '');

		if ($login === '') {
			$errors['login'] = 'Поле "Логин" обязательно для заполнения';
		} elseif (strlen($login) < 3 || strlen($login) > 64) {
			$errors['login'] = 'Логин должен содержать от 3 до 64 символов';
		} elseif (!preg_match('/^[a-zA-Z0-9_.@-]+$/', $login)) {
			$errors['login'] = 'Логин содержит недопустимые символы';
		}

		if ($password === '') {
			$errors['password'] = 'Поле "Пароль" обязательно для заполнения';
		} elseif (strlen($password) < 4 || strlen($password) > 128) {
			$errors['password'] = 'Пароль должен содержать от 4 до 128 символов';
		}

		return [
			'is_valid' => empty($errors),
			'errors' => $errors,
			'data' => [
				'login' => $login,
				'password' => $password,
			],
		];
	}
}
