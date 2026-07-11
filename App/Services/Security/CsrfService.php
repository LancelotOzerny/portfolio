<?php

namespace App\Services\Security;

class CsrfService
{
	private const SESSION_KEY = '_csrf_token';

	public function getToken(): string
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$token = $_SESSION[self::SESSION_KEY] ?? '';
		if (!is_string($token) || $token === '') {
			$token = bin2hex(random_bytes(32));
			$_SESSION[self::SESSION_KEY] = $token;
		}

		return $token;
	}

	public function validate(?string $token): bool
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		$expected = $_SESSION[self::SESSION_KEY] ?? '';
		if (!is_string($expected) || $expected === '' || !is_string($token) || $token === '') {
			return false;
		}

		return hash_equals($expected, $token);
	}
}
