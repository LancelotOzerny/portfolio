<?php

namespace App\Services\Blog;

use Throwable;

class BlogViewerIdentity
{
	private const COOKIE_NAME = 'ls_blog_viewer';
	private const COOKIE_TTL = 31536000;

	public function resolveKey(): string
	{
		$existing = trim((string) ($_COOKIE[self::COOKIE_NAME] ?? ''));
		if (preg_match('/^[a-f0-9]{32}$/', $existing) === 1) {
			return $existing;
		}

		try {
			$key = bin2hex(random_bytes(16));
		} catch (Throwable) {
			return '';
		}

		$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

		setcookie(self::COOKIE_NAME, $key, [
			'expires' => time() + self::COOKIE_TTL,
			'path' => '/',
			'secure' => $secure,
			'httponly' => true,
			'samesite' => 'Lax',
		]);

		$_COOKIE[self::COOKIE_NAME] = $key;

		return $key;
	}

	public function resolveClientIp(): string
	{
		$candidates = [];

		foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $header) {
			$value = trim((string) ($_SERVER[$header] ?? ''));
			if ($value === '') {
				continue;
			}

			if ($header === 'HTTP_X_FORWARDED_FOR') {
				foreach (explode(',', $value) as $part) {
					$candidates[] = trim($part);
				}
				continue;
			}

			$candidates[] = $value;
		}

		foreach ($candidates as $candidate) {
			if (filter_var($candidate, FILTER_VALIDATE_IP) !== false) {
				return $candidate;
			}
		}

		return '';
	}
}
