<?php

namespace Controllers\Api;

use App\Services\Gallery\GalleryStorageService;
use Modules\Main\Auth;
use Throwable;

class GalleryController
{
	public function list(): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!Auth::getInstance()->isAdmin()) {
			$this->respond(false, 'Недостаточно прав.');
			return;
		}

		try {
			$albums = (new GalleryStorageService())->getAlbums();
			$items = [];

			foreach ($albums as $album) {
				foreach ($album['photos'] as $photo) {
					$items[] = [
						'path' => $photo['path'],
						'name' => $photo['name'],
						'album' => $album['name'],
					];
				}
			}

			echo json_encode([
				'success' => true,
				'albums' => $albums,
				'items' => $items,
			], JSON_UNESCAPED_UNICODE);
		} catch (Throwable $exception) {
			$message = trim($exception->getMessage());
			$this->respond(false, $message !== '' ? $message : 'Не удалось загрузить галерею.');
		}
	}

	private function respond(bool $success, string $message): void
	{
		echo json_encode([
			'success' => $success,
			'message' => $message,
		], JSON_UNESCAPED_UNICODE);
	}
}
