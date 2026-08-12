<?php

namespace App\Services\Blog;

class BlogDateFormatter
{
	private const MONTHS = [
		1 => 'января',
		2 => 'февраля',
		3 => 'марта',
		4 => 'апреля',
		5 => 'мая',
		6 => 'июня',
		7 => 'июля',
		8 => 'августа',
		9 => 'сентября',
		10 => 'октября',
		11 => 'ноября',
		12 => 'декабря',
	];

	public function format(?string $value): string
	{
		$value = trim((string) $value);
		if ($value === '') {
			return '';
		}

		if (preg_match('~^\d{1,2}\s+[а-яё]+\s+\d{4}$~ui', $value)) {
			return $value;
		}

		$timestamp = strtotime($value);
		if ($timestamp === false) {
			return $value;
		}

		$day = (int) date('j', $timestamp);
		$month = self::MONTHS[(int) date('n', $timestamp)] ?? date('m', $timestamp);
		$year = date('Y', $timestamp);

		return $day . ' ' . $month . ' ' . $year;
	}

	public function formatWithTime(?string $value): string
	{
		$date = $this->format($value);
		if ($date === '') {
			return '';
		}

		$timestamp = strtotime((string) $value);
		if ($timestamp === false) {
			return $date;
		}

		return $date . ' ' . date('H:i', $timestamp);
	}
}
