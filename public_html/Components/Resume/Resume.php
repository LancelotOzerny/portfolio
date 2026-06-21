<?php

namespace Components\Resume;

use App\Services\Resume\DescriptionSanitizer;
use DateInterval;
use DateTimeImmutable;
use Models\WorkExperienceModel;
use Modules\Main\BaseComponent;
use Throwable;

class Resume extends BaseComponent
{
	private const MONTHS = [
		1 => 'Январь',
		2 => 'Февраль',
		3 => 'Март',
		4 => 'Апрель',
		5 => 'Май',
		6 => 'Июнь',
		7 => 'Июль',
		8 => 'Август',
		9 => 'Сентябрь',
		10 => 'Октябрь',
		11 => 'Ноябрь',
		12 => 'Декабрь',
	];

	protected function prepareData(array $params = []): void
	{
		$showExperience = filter_var($params['show_experience'] ?? false, FILTER_VALIDATE_BOOL);
		$items = [];

		if ($showExperience) {
			try {
				$items = (new WorkExperienceModel())->findAllActiveOrdered();
			} catch (Throwable) {
				$items = [];
			}
		}

		$sanitizer = new DescriptionSanitizer();
		foreach ($items as $item) {
			$isCurrent = (int) ($item->is_current ?? 0) === 1;
			$item->date_label = $this->formatMonth((string) ($item->start_date ?? ''))
				. ' — '
				. ($isCurrent ? $this->formatMonth((new DateTimeImmutable())->format('Y-m-01')) : $this->formatMonth((string) ($item->end_date ?? '')));
			$item->description = $sanitizer->sanitize((string) ($item->description ?? ''));
		}

		$this->setParam('show_experience', $showExperience);
		$this->setParam('experience', $items);
		$this->setParam('experience_duration', $this->calculateDuration($items));
	}

	private function formatMonth(string $value): string
	{
		if (trim($value) === '') {
			return '';
		}

		try {
			$date = new DateTimeImmutable($value);
		} catch (Throwable) {
			return '';
		}

		return self::MONTHS[(int) $date->format('n')] . ' ' . $date->format('Y');
	}

	private function calculateDuration(array $items): string
	{
		$months = [];
		$currentMonth = new DateTimeImmutable('first day of this month');

		foreach ($items as $item) {
			$startValue = trim((string) ($item->start_date ?? ''));
			$isCurrent = (int) ($item->is_current ?? 0) === 1;
			$endValue = trim((string) ($item->end_date ?? ''));
			if ($startValue === '' || (!$isCurrent && $endValue === '')) {
				continue;
			}

			try {
				$start = new DateTimeImmutable($startValue);
				$end = $isCurrent
					? $currentMonth
					: new DateTimeImmutable($endValue);
			} catch (Throwable) {
				continue;
			}

			for ($month = $start->modify('first day of this month'); $month <= $end; $month = $month->add(new DateInterval('P1M'))) {
				$months[$month->format('Y-m')] = true;
			}
		}

		$count = count($months);
		if ($count === 0) {
			return '';
		}

		$years = intdiv($count, 12);
		$remainingMonths = $count % 12;
		$parts = [];
		if ($years > 0) {
			$parts[] = $years . ' ' . $this->plural($years, 'год', 'года', 'лет');
		}
		if ($remainingMonths > 0) {
			$parts[] = $remainingMonths . ' ' . $this->plural($remainingMonths, 'месяц', 'месяца', 'месяцев');
		}

		return implode(' ', $parts);
	}

	private function plural(int $number, string $one, string $few, string $many): string
	{
		$lastTwo = $number % 100;
		if ($lastTwo >= 11 && $lastTwo <= 19) {
			return $many;
		}

		return match ($number % 10) {
			1 => $one,
			2, 3, 4 => $few,
			default => $many,
		};
	}
}
