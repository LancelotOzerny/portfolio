<?php

namespace Controllers\Api;

use Modules\Main\App;
use Modules\Main\Auth;

class ImagesController
{
	private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'];

	public function list(): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!Auth::getInstance()->isAdmin()) {
			$this->respond(false, 'Недостаточно прав.');
			return;
		}

		$root = App::getInstance()->root . '/public_html/upload';
		$images = is_dir($root) ? $this->collectImages($root, '/upload') : [];

		echo json_encode([
			'success' => true,
			'items' => $images,
		], JSON_UNESCAPED_UNICODE);
	}

	private function collectImages(string $directory, string $publicPrefix): array
	{
		$items = [];
		$entries = scandir($directory);

		if ($entries === false) {
			return $items;
		}

		foreach ($entries as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}

			$fullPath = $directory . '/' . $entry;
			$publicPath = $publicPrefix . '/' . $entry;

			if (is_dir($fullPath)) {
				$items = array_merge($items, $this->collectImages($fullPath, $publicPath));
				continue;
			}

			if (!$this->isAllowedImage($entry)) {
				continue;
			}

			$items[] = [
				'path' => $publicPath,
				'name' => $entry,
			];
		}

		usort($items, static fn(array $a, array $b): int => strcmp($a['path'], $b['path']));

		return $items;
	}

	private function isAllowedImage(string $filename): bool
	{
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		return in_array($extension, self::ALLOWED_EXTENSIONS, true);
	}

	private function respond(bool $success, string $message): void
	{
		echo json_encode([
			'success' => $success,
			'message' => $message,
		], JSON_UNESCAPED_UNICODE);
	}
}
