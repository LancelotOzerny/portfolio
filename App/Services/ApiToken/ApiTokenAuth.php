<?php

namespace App\Services\ApiToken;

use Modules\Main\Auth;

final class ApiTokenAuth
{
	public function __construct(
		private readonly ApiTokenService $tokenService = new ApiTokenService(),
	) {
	}

	public function resolveUser(): ?object
	{
		$token = $this->extractBearerToken();
		if ($token !== '') {
			$user = $this->tokenService->findUserByToken($token);
			if ($user !== null) {
				return $user;
			}
		}

		return Auth::getInstance()->getCurrentUserData();
	}

	private function extractBearerToken(): string
	{
		$header = trim((string) ($this->authorizationHeader() ?? ''));
		if ($header === '') {
			return '';
		}

		if (preg_match('/^Bearer\s+(\S+)/i', $header, $matches) !== 1) {
			return '';
		}

		return trim((string) ($matches[1] ?? ''));
	}

	private function authorizationHeader(): ?string
	{
		if (isset($_SERVER['HTTP_AUTHORIZATION']) && is_string($_SERVER['HTTP_AUTHORIZATION'])) {
			return $_SERVER['HTTP_AUTHORIZATION'];
		}

		if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && is_string($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
			return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
		}

		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (is_array($headers)) {
				foreach ($headers as $name => $value) {
					if (strtolower((string) $name) === 'authorization') {
						return is_string($value) ? $value : null;
					}
				}
			}
		}

		return null;
	}
}
