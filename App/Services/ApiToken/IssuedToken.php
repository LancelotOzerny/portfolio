<?php

namespace App\Services\ApiToken;

final class IssuedToken
{
	public function __construct(
		public readonly string $token,
		public readonly string $expiresAt,
	) {
	}
}
