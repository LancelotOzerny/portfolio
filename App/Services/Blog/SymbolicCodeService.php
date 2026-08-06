<?php

namespace App\Services\Blog;

class SymbolicCodeService
{
	private const TRANSLIT_MAP = [
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
		'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
		'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
		'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
		'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
		'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
		'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
		'А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'g', 'Д' => 'd',
		'Е' => 'e', 'Ё' => 'yo', 'Ж' => 'zh', 'З' => 'z', 'И' => 'i',
		'Й' => 'y', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
		'О' => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't',
		'У' => 'u', 'Ф' => 'f', 'Х' => 'h', 'Ц' => 'ts', 'Ч' => 'ch',
		'Ш' => 'sh', 'Щ' => 'sch', 'Ъ' => '', 'Ы' => 'y', 'Ь' => '',
		'Э' => 'e', 'Ю' => 'yu', 'Я' => 'ya',
	];

	public function fromTitle(string $title): string
	{
		return $this->normalize($title);
	}

	public function normalize(string $value): string
	{
		$value = trim($value);
		if ($value === '') {
			return '';
		}

		$value = strtr($value, self::TRANSLIT_MAP);
		$value = strtolower($value);
		$value = preg_replace('/[^a-z0-9_-]+/', '-', $value) ?? '';
		$value = preg_replace('/-+/', '-', $value) ?? '';
		$value = trim($value, '-_');

		return $value;
	}

	public function isValid(string $code): bool
	{
		return $code !== '' && preg_match('/^[a-zA-Z0-9_-]+$/', $code) === 1;
	}

	public function resolvePublicSegment(?string $code, int $id): string
	{
		$code = trim((string) $code);
		if ($code !== '' && $this->isValid($code)) {
			return $code;
		}

		return (string) $id;
	}
}
