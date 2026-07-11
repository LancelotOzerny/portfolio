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
		if (mb_substr($truncated, -1) !== ' ') {
			$truncated .= '...';
		} else {
			$truncated = rtrim($truncated);
		}

		return $truncated;
	}
}
