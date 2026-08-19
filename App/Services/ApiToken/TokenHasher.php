<?php

namespace App\Services\ApiToken;

final class TokenHasher
{
	public function hash(string $token): string
	{
		return hash('sha256', $token);
	}
}
