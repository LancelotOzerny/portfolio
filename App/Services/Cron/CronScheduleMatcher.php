<?php

namespace App\Services\Cron;

use DateTimeInterface;

final class CronScheduleMatcher
{
	public function matches(array $schedule, DateTimeInterface $time): bool
	{
		return $this->fieldMatches((string) ($schedule['minute'] ?? '*'), (int) $time->format('i'), 0, 59)
			&& $this->fieldMatches((string) ($schedule['hour'] ?? '*'), (int) $time->format('G'), 0, 23)
			&& $this->fieldMatches((string) ($schedule['day'] ?? '*'), (int) $time->format('j'), 1, 31)
			&& $this->fieldMatches((string) ($schedule['month'] ?? '*'), (int) $time->format('n'), 1, 12)
			&& $this->fieldMatches((string) ($schedule['weekday'] ?? '*'), (int) $time->format('w'), 0, 6);
	}

	public function format(array $schedule): string
	{
		return implode(' ', [
			(string) ($schedule['minute'] ?? '*'),
			(string) ($schedule['hour'] ?? '*'),
			(string) ($schedule['day'] ?? '*'),
			(string) ($schedule['month'] ?? '*'),
			(string) ($schedule['weekday'] ?? '*'),
		]);
	}

	private function fieldMatches(string $expression, int $value, int $min, int $max): bool
	{
		$expression = trim($expression);
		if ($expression === '') {
			$expression = '*';
		}

		foreach (explode(',', $expression) as $part) {
			if ($this->partMatches(trim($part), $value, $min, $max)) {
				return true;
			}
		}

		return false;
	}

	private function partMatches(string $part, int $value, int $min, int $max): bool
	{
		if ($part === '*') {
			return true;
		}

		if (preg_match('/^\*\/(\d+)$/', $part, $matches) === 1) {
			$step = (int) $matches[1];

			return $step > 0 && (($value - $min) % $step) === 0;
		}

		if (preg_match('/^(\d+)-(\d+)(?:\/(\d+))?$/', $part, $matches) === 1) {
			$start = (int) $matches[1];
			$end = (int) $matches[2];
			$step = isset($matches[3]) ? (int) $matches[3] : 1;

			if ($step <= 0 || $start > $end) {
				return false;
			}

			for ($current = $start; $current <= $end; $current += $step) {
				if ($current === $value) {
					return true;
				}
			}

			return false;
		}

		if (ctype_digit($part)) {
			return (int) $part === $value;
		}

		return false;
	}
}
