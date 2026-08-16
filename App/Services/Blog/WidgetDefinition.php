<?php

namespace App\Services\Blog;

final class WidgetDefinition
{
	public function __construct(
		public readonly string $id,
		public readonly string $title,
		public readonly string $htmlPath,
		public readonly string $cssUrl,
		public readonly string $jsUrl,
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
}
