<?php

namespace App\Services\Gallery;

use InvalidArgumentException;
use Modules\Main\App;
use RuntimeException;

class GalleryStorageService
{
	public const PUBLIC_PREFIX = '/upload/gallery';

	private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

	private const ALLOWED_MIME_TO_EXT = [
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/gif' => 'gif',
		'image/webp' => 'webp',
	];

	public function getRootPath(): string
	{
		return App::getInstance()->root . '/public_html/upload/gallery';
	}

	public function ensureRootExists(): void
	{
		$root = $this->getRootPath();
		if (is_dir($root)) {
			return;
		}

		if (!mkdir($root, 0775, true) && !is_dir($root)) {
			throw new RuntimeException('Не удалось создать директорию фотогалереи.');
		}
	}

	public function getAlbums(): array
	{
		$this->ensureRootExists();
		$root = $this->getRootPath();
		$entries = scandir($root);

		if ($entries === false) {
			return [];
		}

		$albums = [];

		foreach ($entries as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}

			$albumPath = $root . DIRECTORY_SEPARATOR . $entry;
			if (!is_dir($albumPath)) {
				continue;
			}

			$albums[] = [
				'name' => $entry,
				'photos' => $this->collectPhotos($albumPath, self::PUBLIC_PREFIX . '/' . rawurlencode($entry)),
			];
		}

		usort($albums, static fn(array $left, array $right): int => strcmp($left['name'], $right['name']));

		return $albums;
	}

	public function createAlbum(string $name): void
	{
		$albumName = $this->sanitizeAlbumName($name);
		$this->ensureRootExists();

		$albumPath = $this->getRootPath() . DIRECTORY_SEPARATOR . $albumName;
		if (is_dir($albumPath)) {
			throw new InvalidArgumentException('Альбом с таким названием уже существует.');
		}

		if (!mkdir($albumPath, 0775, true) && !is_dir($albumPath)) {
			throw new RuntimeException('Не удалось создать альбом.');
		}
	}

	public function uploadPhoto(string $album, array $file): string
	{
		$albumName = $this->sanitizeAlbumName($album);
		$albumPath = $this->resolveAlbumPath($albumName);

		if (!is_dir($albumPath)) {
			throw new InvalidArgumentException('Альбом не найден.');
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode === UPLOAD_ERR_NO_FILE) {
			throw new InvalidArgumentException('Файл не выбран.');
		}

		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new RuntimeException('Ошибка загрузки файла.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new RuntimeException('Некорректный загруженный файл.');
		}

		$mime = $this->detectImageMimeType($tmpPath);
		if (!isset(self::ALLOWED_MIME_TO_EXT[$mime])) {
			throw new InvalidArgumentException('Допустимы только JPG, PNG, GIF и WEBP.');
		}

		$fileName = sprintf('%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(4)), self::ALLOWED_MIME_TO_EXT[$mime]);
		$targetPath = $albumPath . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new RuntimeException('Не удалось сохранить файл.');
		}

		return self::PUBLIC_PREFIX . '/' . rawurlencode($albumName) . '/' . rawurlencode($fileName);
	}

	public function deletePhoto(string $album, string $filename): void
	{
		$albumName = $this->sanitizeAlbumName($album);
		$fileName = $this->sanitizeFileName($filename);
		$filePath = $this->resolveAlbumPath($albumName) . DIRECTORY_SEPARATOR . $fileName;

		if (!is_file($filePath)) {
			throw new InvalidArgumentException('Файл не найден.');
		}

		if (!unlink($filePath)) {
			throw new RuntimeException('Не удалось удалить файл.');
		}
	}

	private function collectPhotos(string $albumPath, string $publicPrefix): array
	{
		$entries = scandir($albumPath);
		if ($entries === false) {
			return [];
		}

		$photos = [];

		foreach ($entries as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}

			$fullPath = $albumPath . DIRECTORY_SEPARATOR . $entry;
			if (!is_file($fullPath) || !$this->isAllowedImage($entry)) {
				continue;
			}

			$photos[] = [
				'name' => $entry,
				'path' => $publicPrefix . '/' . rawurlencode($entry),
			];
		}

		usort($photos, static fn(array $left, array $right): int => strcmp($left['name'], $right['name']));

		return $photos;
	}

	private function resolveAlbumPath(string $albumName): string
	{
		$albumPath = $this->getRootPath() . DIRECTORY_SEPARATOR . $albumName;
		$realRoot = realpath($this->getRootPath());
		$realAlbum = realpath($albumPath);

		if ($realRoot === false || $realAlbum === false || !str_starts_with($realAlbum, $realRoot)) {
			throw new InvalidArgumentException('Альбом не найден.');
		}

		return $realAlbum;
	}

	private function sanitizeAlbumName(string $name): string
	{
		$name = trim(str_replace(['\\', '/'], '', $name));

		if ($name === '' || $name === '.' || $name === '..' || str_contains($name, '..')) {
			throw new InvalidArgumentException('Некорректное название альбома.');
		}

		return $name;
	}

	private function sanitizeFileName(string $filename): string
	{
		$filename = basename(str_replace('\\', '/', $filename));

		if ($filename === '' || $filename === '.' || $filename === '..' || !$this->isAllowedImage($filename)) {
			throw new InvalidArgumentException('Некорректное имя файла.');
		}

		return $filename;
	}

	private function isAllowedImage(string $filename): bool
	{
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		return in_array($extension, self::ALLOWED_EXTENSIONS, true);
	}

	private function detectImageMimeType(string $filePath): string
	{
		$mime = '';

		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			if ($finfo !== false) {
				$detected = finfo_file($finfo, $filePath);
				finfo_close($finfo);

				if (is_string($detected)) {
					$mime = $detected;
				}
			}
		}

		if ($mime === '' && function_exists('mime_content_type')) {
			$detected = mime_content_type($filePath);
			if (is_string($detected)) {
				$mime = $detected;
			}
		}

		if ($mime === '' && function_exists('getimagesize')) {
			$imageInfo = @getimagesize($filePath);
			if (is_array($imageInfo) && isset($imageInfo['mime']) && is_string($imageInfo['mime'])) {
				$mime = $imageInfo['mime'];
			}
		}

		return strtolower(trim($mime));
	}
}
