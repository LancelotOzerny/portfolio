<?php

namespace App\Services\ContentEditor;

use RuntimeException;

class ContentEditorUploadService
{
	/**
	 * @return array<string, string>
	 */
	private function allowedImageMimeToExt(): array
	{
		return [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/webp' => 'webp',
		];
	}

	public function saveImage(int $entityId, string $fileKey, string $directory, string $filePrefix): string
	{
		$file = $_FILES[$fileKey] ?? null;
		if (!is_array($file)) {
			throw new RuntimeException('Image file was not sent.');
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new RuntimeException('Image upload error.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new RuntimeException('Invalid uploaded image.');
		}

		$mime = $this->detectMimeType($tmpPath);
		$allowedMimeToExt = $this->allowedImageMimeToExt();
		if (!isset($allowedMimeToExt[$mime])) {
			throw new RuntimeException('Only JPG/PNG/GIF/WEBP images are allowed.');
		}

		$uploadDir = $this->ensureUploadDirectory($directory);
		$fileName = sprintf(
			'%s_%d_%s_%s.%s',
			$filePrefix,
			$entityId,
			date('Ymd_His'),
			bin2hex(random_bytes(4)),
			$allowedMimeToExt[$mime]
		);
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new RuntimeException('Unable to save uploaded image.');
		}

		return $this->publicUrl($directory, $fileName);
	}

	/**
	 * @return array{url: string, name: string, extension: string}
	 */
	public function saveFile(int $entityId, string $fileKey, string $directory, string $filePrefix): array
	{
		$file = $_FILES[$fileKey] ?? null;
		if (!is_array($file)) {
			throw new RuntimeException('File was not sent.');
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new RuntimeException('File upload error.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new RuntimeException('Invalid uploaded file.');
		}

		$originalName = trim((string) ($file['name'] ?? ''));
		$extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
		$allowedExtensions = ['docx', 'pdf', 'txt'];
		if (!in_array($extension, $allowedExtensions, true)) {
			throw new RuntimeException('Only DOCX/TXT/PDF files are allowed.');
		}

		$mime = $this->detectMimeType($tmpPath);
		$allowedMimeByExtension = [
			'docx' => [
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/zip',
				'application/octet-stream',
			],
			'pdf' => [
				'application/pdf',
				'application/x-pdf',
				'application/octet-stream',
			],
			'txt' => [
				'text/plain',
				'text/x-plain',
				'application/octet-stream',
			],
		];

		if ($mime !== '' && !in_array($mime, $allowedMimeByExtension[$extension], true)) {
			throw new RuntimeException('Uploaded file type does not match its extension.');
		}

		$uploadDir = $this->ensureUploadDirectory($directory);
		$fileName = sprintf(
			'%s_%d_file_%s_%s.%s',
			$filePrefix,
			$entityId,
			date('Ymd_His'),
			bin2hex(random_bytes(4)),
			$extension
		);
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new RuntimeException('Unable to save uploaded file.');
		}

		return [
			'url' => $this->publicUrl($directory, $fileName),
			'name' => $originalName !== '' ? $originalName : $fileName,
			'extension' => $extension,
		];
	}

	private function ensureUploadDirectory(string $directory): string
	{
		$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
		if ($documentRoot === '') {
			throw new RuntimeException('Document root is not configured.');
		}

		$relative = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, trim($directory, '/\\'));
		$uploadDir = $documentRoot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $relative;
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
			throw new RuntimeException('Unable to create upload directory.');
		}

		return $uploadDir;
	}

	private function publicUrl(string $directory, string $fileName): string
	{
		$relative = trim(str_replace('\\', '/', $directory), '/');

		return '/upload/' . $relative . '/' . $fileName;
	}

	private function detectMimeType(string $filePath): string
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
