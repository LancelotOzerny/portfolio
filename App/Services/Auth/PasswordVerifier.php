<?php

namespace App\Services\Auth;

final class PasswordVerifier
{
	public function verify(string $password, object $user): bool
	{
		$storedPassword = $this->getStoredPassword($user);
		if ($storedPassword === null) {
			return false;
		}

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
}
