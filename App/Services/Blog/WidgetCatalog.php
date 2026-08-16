<?php

namespace App\Services\Blog;

use Modules\Main\App;

class WidgetCatalog
{
	/** @var array<string, WidgetDefinition>|null */
	private ?array $widgets = null;

	public function __construct(private readonly string $rootPath = '')
	{
	}

	/**
	 * @return list<WidgetDefinition>
	 */
	public function all(): array
	{
		$this->load();

		return array_values($this->widgets ?? []);
	}

	public function find(string $id): ?WidgetDefinition
	{
		$this->load();

		return ($this->widgets ?? [])[$id] ?? null;
	}

	private function load(): void
	{
		if ($this->widgets !== null) {
			return;
		}

		$this->widgets = [];
		$root = $this->widgetsRoot();
		if (!is_dir($root)) {
			return;
		}

		foreach (scandir($root) ?: [] as $name) {
			if ($name === '.' || $name === '..' || preg_match('/^[a-zA-Z0-9_-]+$/', $name) !== 1) {
				continue;
			}

			$dir = $root . '/' . $name;
			if (!is_dir($dir)) {
				continue;
			}

			$definition = $this->readDefinition($dir, $name);
			if ($definition === null || isset($this->widgets[$definition->id])) {
				continue;
			}

			$this->widgets[$definition->id] = $definition;
		}

		uasort($this->widgets, static fn (WidgetDefinition $left, WidgetDefinition $right): int => strcasecmp($left->title, $right->title));
	}

	private function readDefinition(string $dir, string $folderName): ?WidgetDefinition
	{
		$jsonFile = $dir . '/widget.json';
		$htmlFile = $dir . '/widget.html';
		if (!is_file($jsonFile) || !is_file($htmlFile)) {
			return null;
		}

		$meta = json_decode((string) file_get_contents($jsonFile), true);
		if (!is_array($meta)) {
			return null;
		}

		$id = strtolower(trim((string) ($meta['id'] ?? '')));
		if (preg_match('/^[a-z0-9-]+$/', $id) !== 1) {
			return null;
		}

		$title = trim((string) ($meta['title'] ?? $folderName));
		$publicDir = '/Widgets/' . $folderName;
		$cssFile = $dir . '/widget.css';
		$jsFile = $dir . '/widget.js';

		return new WidgetDefinition(
			$id,
			$title !== '' ? $title : $folderName,
			$htmlFile,
			is_file($cssFile) ? $publicDir . '/widget.css' : '',
			is_file($jsFile) ? $publicDir . '/widget.js' : '',
		);
	}

	private function widgetsRoot(): string
	{
		if ($this->rootPath !== '') {
			return rtrim($this->rootPath, '/\\');
		}

		return App::getInstance()->root . '/public_html/Widgets';
	}
}
