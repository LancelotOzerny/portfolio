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
			$this->readFields($meta['fields'] ?? null),
		);
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	private function readFields(mixed $source): array
	{
		if (!is_array($source)) {
			return [];
		}

		$fields = [];
		foreach ($source as $item) {
			if (!is_array($item)) {
				continue;
			}

			$name = trim((string) ($item['name'] ?? ''));
			$label = trim((string) ($item['label'] ?? ''));
			$type = trim((string) ($item['type'] ?? 'number'));
			if (preg_match('/^[a-z][a-z0-9_]*$/', $name) !== 1 || $label === '' || !in_array($type, ['number', 'select', 'text', 'rows'], true)) {
				continue;
			}

			$field = [
				'name' => $name,
				'label' => $label,
				'type' => $type,
			];

			if ($type === 'number') {
				foreach (['min', 'max', 'step'] as $key) {
					if (isset($item[$key]) && is_numeric($item[$key])) {
						$field[$key] = 0 + $item[$key];
					}
				}
			}

			if ($type === 'text') {
				$maxlength = isset($item['maxlength']) && is_numeric($item['maxlength']) ? (int) $item['maxlength'] : 80;
				$field['maxlength'] = max(1, min(120, $maxlength));
			}

			if ($type === 'rows') {
				$maxRows = isset($item['max']) && is_numeric($item['max']) ? (int) $item['max'] : 24;
				$field['max'] = max(2, min(50, $maxRows));
				$field['x_label'] = $this->fieldCaption($item['x_label'] ?? null, 'X');
				$field['y_label'] = $this->fieldCaption($item['y_label'] ?? null, 'Y');
			}

			if ($type === 'select') {
				$options = [];
				foreach (is_array($item['options'] ?? null) ? $item['options'] : [] as $option) {
					if (!is_array($option)) {
						continue;
					}

					$value = trim((string) ($option['value'] ?? ''));
					$optionLabel = trim((string) ($option['label'] ?? ''));
					if (preg_match('/^[a-z0-9_-]+$/', $value) !== 1 || $optionLabel === '') {
						continue;
					}

					$options[] = [
						'value' => $value,
						'label' => $optionLabel,
					];
				}

				if ($options === []) {
					continue;
				}

				$field['options'] = $options;
			}

			$fields[] = $field;
		}

		return $fields;
	}

	private function fieldCaption(mixed $value, string $fallback): string
	{
		$caption = trim(strip_tags((string) $value));
		if ($caption === '') {
			return $fallback;
		}

		if (mb_strlen($caption) > 20) {
			$caption = mb_substr($caption, 0, 20);
		}

		return $caption;
	}

	private function widgetsRoot(): string
	{
		if ($this->rootPath !== '') {
			return rtrim($this->rootPath, '/\\');
		}

		return App::getInstance()->root . '/public_html/Widgets';
	}
}
