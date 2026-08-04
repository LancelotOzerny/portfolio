<?php

namespace Components\ImagePreview;

use Modules\Main\BaseComponent;

class ImagePreview extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$relativePath = $this->normalizePath((string) ($params['path'] ?? ''));

		$this->params = $params;
		$this->setParam('path', $relativePath);
		$this->setParam('alt', $this->params['alt'] ?? '');
		$this->setParam('title', $this->params['title'] ?? '');
	}

	protected function getEditableParamKeys(): array
	{
		return ['path', 'alt', 'title'];
	}

	protected function getEditDisplayClass(): string
	{
		return 'component-edit_fit';
	}

	private function normalizePath(string $path): string
	{
		$path = trim(str_replace('\\', '/', $path), '/');
		if ($path === '' || str_contains($path, '..')) {
			return '';
		}

		if ($path[0] !== '/') {
			$path = '/' . $path;
		}

		return preg_match('~^/[a-zA-Z0-9/_\.-]+$~', $path) ? $path : '';
	}
}
