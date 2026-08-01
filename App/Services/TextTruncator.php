<?php

namespace App\Services;

class TextTruncator
{
	public function truncate(string $text, int $limit = 300): string
	{
		$text = trim($text);
		if ($text === '' || mb_strlen($text) <= $limit) {
			return $text;
		}

		$truncated = mb_substr($text, 0, $limit);
		$truncated = rtrim($truncated) . '...';

		return $truncated;
	}
}
