<?php

namespace App\Services\Todo;

final class TodoTaskPriority
{
	public const RANK_IMPORTANT_URGENT = 0;
	public const RANK_URGENT = 1;
	public const RANK_IMPORTANT = 2;
	public const RANK_NORMAL = 3;

	public static function rank(bool $important, bool $urgent): int
	{
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

	/**
	 * @param array{important?: bool, urgent?: bool, sort_order?: int, id?: int} $a
	 * @param array{important?: bool, urgent?: bool, sort_order?: int, id?: int} $b
	 */
	public static function compare(array $a, array $b): int
	{
		$rankCompare = self::rank(!empty($a['important']), !empty($a['urgent']))
			<=> self::rank(!empty($b['important']), !empty($b['urgent']));

		if ($rankCompare !== 0) {
			return $rankCompare;
		}

		$sortCompare = ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0));
		if ($sortCompare !== 0) {
			return $sortCompare;
		}

		return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
	}

	/**
	 * @param list<array{important?: bool, urgent?: bool, sort_order?: int, id?: int}> $items
	 * @return list<array{important?: bool, urgent?: bool, sort_order?: int, id?: int}>
	 */
	public static function sort(array $items): array
	{
		usort($items, [self::class, 'compare']);

		return $items;
	}
}
