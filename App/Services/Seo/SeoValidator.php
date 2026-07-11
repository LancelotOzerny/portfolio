<?php

namespace App\Services\Seo;

use InvalidArgumentException;

class SeoValidator
{
	public function validatePageForm(array $input): array
	{
		$title = $this->nullableString($input['title'] ?? null, 255);
		$description = $this->nullableString($input['description'] ?? null, 320);
		$canonicalUrl = $this->nullableString($input['canonical_url'] ?? null, 500);
		$ogTitle = $this->nullableString($input['og_title'] ?? null, 255);
		$ogDescription = $this->nullableString($input['og_description'] ?? null, 320);
		$ogImage = $this->nullableString($input['og_image'] ?? null, 500);

		if ($canonicalUrl !== null && !$this->isValidCanonical($canonicalUrl)) {
			throw new InvalidArgumentException('Укажите корректный canonical URL.');
		}

		if ($ogImage !== null && !$this->isValidImageReference($ogImage)) {
			throw new InvalidArgumentException('Укажите корректный URL изображения для Open Graph.');
		}

		return [
			'title' => $title,
			'description' => $description,
			'canonical_url' => $canonicalUrl,
			'robots_index' => isset($input['robots_index']) ? 1 : 0,
			'robots_follow' => isset($input['robots_follow']) ? 1 : 0,
			'og_title' => $ogTitle,
			'og_description' => $ogDescription,
			'og_image' => $ogImage,
		];
	}

	private function nullableString(mixed $value, int $maxLength): ?string
	{
		$value = trim((string) $value);
		if ($value === '') {
			return null;
		}

		if (mb_strlen($value) > $maxLength) {
			throw new InvalidArgumentException('Превышена максимальная длина поля.');
		}

		return $value;
	}

	private function isValidCanonical(string $url): bool
	{
		if (str_starts_with($url, '/')) {
			return preg_match('~^/[A-Za-z0-9/_\-.%]*$~', $url) === 1;
		}

		return filter_var($url, FILTER_VALIDATE_URL) !== false
			&& preg_match('~^https?://~i', $url) === 1;
	}

	private function isValidImageReference(string $url): bool
	{
		if (str_starts_with($url, '/')) {
			return preg_match('~^/upload/images/[A-Za-z0-9/_\-.]+$~', $url) === 1;
		}

		return filter_var($url, FILTER_VALIDATE_URL) !== false
			&& preg_match('~^https?://~i', $url) === 1;
	}
}
