<?php

namespace App\Services\Blog;

final class WidgetDefinition
{
	/**
	 * @param list<array<string, mixed>> $fields
	 */
	public function __construct(
		public readonly string $id,
		public readonly string $title,
		public readonly string $htmlPath,
		public readonly string $cssUrl,
		public readonly string $jsUrl,
		public readonly array $fields = [],
	) {
	}

	public function html(): string
	{
		if ($this->htmlPath === '' || !is_file($this->htmlPath)) {
			return '';
		}

		$contents = file_get_contents($this->htmlPath);
		return $contents === false ? '' : trim($contents);
	}

	/**
	 * @return array{id: string, title: string, fields: list<array<string, mixed>>}
	 */
	public function editorData(): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'fields' => $this->fields,
		];
	}
}
