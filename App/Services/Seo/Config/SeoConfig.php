<?php

namespace App\Services\Seo\Config;

use Modules\Main\Config;

class SeoConfig
{
	/** @var array<string, mixed>|null */
	private ?array $config = null;

	/**
	 * @return array<string, mixed>
	 */
	public function all(): array
	{
		return $this->load();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getSite(): array
	{
		$config = $this->load();

		return is_array($config['site'] ?? null) ? $config['site'] : [];
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function getPages(): array
	{
		$config = $this->load();
		$pages = $config['pages'] ?? [];

		return is_array($pages) ? $pages : [];
	}

	public function hasPage(string $pageKey): bool
	{
		return array_key_exists($pageKey, $this->getPages());
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function getPage(string $pageKey): ?array
	{
		$pages = $this->getPages();

		return $pages[$pageKey] ?? null;
	}

	public function getPagePath(string $pageKey): string
	{
		$page = $this->getPage($pageKey);

		return (string) ($page['path'] ?? '/');
	}

	/**
	 * @return array<string, mixed>
	 */
	private function load(): array
	{
		if ($this->config !== null) {
			return $this->config;
		}

		$this->config = Config::getInstance()->get('App', 'seo')->toArray();

		return $this->config;
	}
}
