<?php

namespace Controllers\Api;

use Modules\Main\App;
use Modules\Main\Auth;

class IncludeAreaController
{
	public function save(): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!Auth::getInstance()->isAdmin()) {
			$this->respond(false, 'Недостаточно прав.');
			return;
		}

		$data = json_decode((string) file_get_contents('php://input'), true);
		if (!is_array($data)) {
			$this->respond(false, 'Некорректный запрос.');
			return;
		}

		$relativePath = $this->normalizePath((string) ($data['path'] ?? ''));
		if ($relativePath === '') {
			$this->respond(false, 'Некорректный путь области.');
			return;
		}

		$root = App::getInstance()->root . '/public_html/Includes';
		$filePath = $root . '/' . $relativePath;
		if (!$this->isPathInsideRoot($filePath, $root)) {
			$this->respond(false, 'Некорректный путь области.');
			return;
		}

		$directory = dirname($filePath);
		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
			$this->respond(false, 'Не удалось создать папку области.');
			return;
		}

		$content = (string) ($data['content'] ?? '');
		if (file_put_contents($filePath, $content, LOCK_EX) === false) {
			$this->respond(false, 'Не удалось сохранить область.');
			return;
		}

		$this->respond(true, 'Сохранено.');
	}

	private function normalizePath(string $path): string
	{
		$path = trim(str_replace('\\', '/', $path), '/');
		if ($path === '' || str_contains($path, '..')) {
			return '';
		}

		return preg_match('~^[a-zA-Z0-9/_\.-]+$~', $path) ? $path : '';
	}

	private function isPathInsideRoot(string $filePath, string $root): bool
	{
		$rootPath = realpath($root);
		if ($rootPath === false) {
			return false;
		}

		$rootPath = rtrim(str_replace('\\', '/', $rootPath), '/') . '/';
		$targetPath = str_replace('\\', '/', $filePath);

		return str_starts_with($targetPath, $rootPath);
	}

	private function respond(bool $success, string $message): void
	{
		echo json_encode([
			'success' => $success,
			'message' => $message,
		], JSON_UNESCAPED_UNICODE);
	}
}
