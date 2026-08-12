<?php

namespace App\Services\Cron;

final class CronTaskPriority
{
	public const RANK_IMPORTANT_URGENT = 0;
	public const RANK_URGENT = 1;
	public const RANK_IMPORTANT = 2;
	public const RANK_NORMAL = 3;

	public static function rank(array $task): int
	{
		$important = !empty($task['important']);
		$urgent = !empty($task['urgent']);

		if ($important && $urgent) {
			return self::RANK_IMPORTANT_URGENT;
		}

		if ($urgent) {
			return self::RANK_URGENT;
		}

		if ($important) {
			return self::RANK_IMPORTANT;
		}

		return self::RANK_NORMAL;
	}

	public static function label(array $task): string
	{
		return match (self::rank($task)) {
			self::RANK_IMPORTANT_URGENT => 'Важно и срочно',
			self::RANK_URGENT => 'Срочно',
			self::RANK_IMPORTANT => 'Важно',
			default => 'Обычная',
		};
	}

	public static function compare(array $a, array $b): int
	{
		$rankCompare = self::rank($a) <=> self::rank($b);
		if ($rankCompare !== 0) {
			return $rankCompare;
		}

		return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
	}

	public static function sort(array $tasks): array
	{
		usort($tasks, [self::class, 'compare']);

		return $tasks;
	}
}
