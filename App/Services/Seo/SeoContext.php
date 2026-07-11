<?php

namespace App\Services\Seo;

use App\Services\Seo\Config\SeoConfig;
use InvalidArgumentException;

class SeoContext
{
	private const ALLOWED_ENTITY_TYPES = ['project'];

	/**
	 * @param array<string, mixed> $entityData
	 */
	private function __construct(
		private readonly string $targetType,
		private readonly string $targetKey,
		private readonly string $path,
		private readonly array $entityData = [],
	) {
	}

	public static function page(string $pageKey): self
	{
		$config = new SeoConfig();
		if (!$config->hasPage($pageKey)) {
			throw new InvalidArgumentException('Unknown SEO page key: ' . $pageKey);
		}

		return new self('page', $pageKey, $config->getPagePath($pageKey));
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public static function custom(string $path, array $data = []): self
	{
		return new self('page', '__custom', $path, $data);
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public static function entity(string $type, string $key, array $data = [], ?string $path = null): self
	{
		$type = trim($type);
		$key = trim($key);

		if (!in_array($type, self::ALLOWED_ENTITY_TYPES, true)) {
			throw new InvalidArgumentException('Unsupported SEO entity type: ' . $type);
		}

		if ($key === '') {
			throw new InvalidArgumentException('SEO entity key is required.');
		}

		return new self($type, $key, $path ?? '/', $data);
	}

	public function getTargetType(): string
	{
		return $this->targetType;
	}

	public function getTargetKey(): string
	{
		return $this->targetKey;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getEntityData(): array
	{
		return $this->entityData;
	}
}
