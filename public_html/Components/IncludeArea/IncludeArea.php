<?php

namespace Components\IncludeArea;

use Modules\Main\App;
use Modules\Main\Auth;
use Modules\Main\BaseComponent;

class IncludeArea extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$relativePath = $this->normalizePath((string) ($params['path'] ?? ''));
		$root = App::getInstance()->root . '/public_html/Includes';
		$filePath = $relativePath !== '' ? $root . '/' . $relativePath : '';

		$this->params = $params;
		$this->setParam('path', $relativePath);
		$this->setParam('content', $this->readContent($filePath, $root));
		$this->setParam('edit_mode', $this->isEditMode());
	}

	private function normalizePath(string $path): string
	{
		$path = trim(str_replace('\\', '/', $path), '/');
		if ($path === '' || str_contains($path, '..')) {
			return '';
		}

		return preg_match('~^[a-zA-Z0-9/_\.-]+$~', $path) ? $path : '';
	}

	private function readContent(string $filePath, string $root): string
	{
		if ($filePath === '' || !$this->isPathInsideRoot($filePath, $root) || !is_file($filePath)) {
			return '';
		}

		$content = file_get_contents($filePath);
		return $content === false ? '' : $content;
	}

	private function isPathInsideRoot(string $filePath, string $root): bool
	{
		$rootPath = realpath($root);
		$targetPath = realpath($filePath);

		if ($rootPath === false || $targetPath === false) {
			$rootPath = rtrim(str_replace('\\', '/', $root), '/') . '/';
			$targetPath = str_replace('\\', '/', $filePath);

			return str_starts_with($targetPath, $rootPath);
		}

		$rootPath = rtrim(str_replace('\\', '/', $rootPath), '/') . '/';
		$targetPath = str_replace('\\', '/', $targetPath);

		return str_starts_with($targetPath, $rootPath);
	}

	private function isEditMode(): bool
	{
		return (string) ($_GET['edit'] ?? '') === 'true' && Auth::getInstance()->isAdmin();
	}
}
