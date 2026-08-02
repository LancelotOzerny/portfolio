<?php

namespace Controllers\Admin;

use Models\BlogTopicsModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class BlogController extends BaseController
{
	private const FLASH_KEY = 'admin_blog_flash';

	public function index(): void
	{
		$this->rubrics();
	}

	public function rubrics(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$flash = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		Template::getInstance()->setParam('title', 'Рубрики блога');
		Template::getInstance()->showHeader();
		$this->render('rubrics', [
			'flash' => is_array($flash) ? $flash : null,
		]);
		Template::getInstance()->showFooter();
	}

	public function articles(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Статьи блога');
		Template::getInstance()->showHeader();
		$this->render('articles');
		Template::getInstance()->showFooter();
	}

	public function create(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Создание рубрики блога');
		Template::getInstance()->showHeader();
		$this->render('edit', [
			'topic' => null,
			'saveSuccess' => false,
			'saveError' => isset($_GET['error']) ? (string) $_GET['error'] : '',
		]);
		Template::getInstance()->showFooter();
	}

	public function store(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$title = trim((string) ($_POST['title'] ?? ''));
		$description = trim((string) ($_POST['description'] ?? ''));
		if ($title === '') {
			header('Location: /admin/content/blog/rubrics/create/?error=' . rawurlencode('Введите название рубрики.'));
			return;
		}

		if (mb_strlen($description) > 500) {
			header('Location: /admin/content/blog/rubrics/create/?error=' . rawurlencode('Preview текст должен быть не длиннее 500 символов.'));
			return;
		}

		$model = new BlogTopicsModel();
		$topicId = 0;

		try {
			$topicId = $model->createForAdmin($title);
		} catch (Throwable) {
		}

		if ($topicId <= 0) {
			header('Location: /admin/content/blog/rubrics/create/?error=' . rawurlencode('Не удалось создать рубрику.'));
			return;
		}

		try {
			$imagePath = $this->saveTopicImageUpload($topicId, 'image_file', '');
				$model->updateEditorData($topicId, $title, $description, $imagePath, isset($_POST['enabled']) ? 1 : 0);
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			if ($message === '') {
				$message = 'Рубрика создана, но часть данных не удалось сохранить.';
			}

			header('Location: /admin/content/blog/rubrics/' . $topicId . '/?error=' . rawurlencode($message));
			return;
		}

		header('Location: /admin/content/blog/rubrics/' . $topicId . '/');
	}

	public function edit(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$topic = (new BlogTopicsModel())->findById($id);
		} catch (Throwable) {
			$topic = null;
		}

		if ($topic === null) {
			header('Location: /admin/content/blog/rubrics/');
			return;
		}

		Template::getInstance()->setParam('title', 'Редактирование рубрики блога ' . $id);
		Template::getInstance()->showHeader();
		$this->render('edit', [
			'topic' => $topic,
			'saveSuccess' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'saveError' => isset($_GET['error']) ? (string) $_GET['error'] : '',
		]);
		Template::getInstance()->showFooter();
	}

	public function update(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$model = new BlogTopicsModel();

		try {
			$topic = $model->findById($id);
		} catch (Throwable) {
			$topic = null;
		}

		if ($topic === null) {
			header('Location: /admin/content/blog/rubrics/');
			return;
		}

		$title = trim((string) ($_POST['title'] ?? ''));
		$description = trim((string) ($_POST['description'] ?? ''));
		$imagePath = trim((string) ($_POST['image_path_existing'] ?? (string) ($topic->image_path ?? '')));
		$enabled = isset($_POST['enabled']) ? 1 : 0;

		if ($title === '') {
			header('Location: /admin/content/blog/rubrics/' . $id . '/?error=' . rawurlencode('Введите название рубрики.'));
			return;
		}

		if (mb_strlen($description) > 500) {
			header('Location: /admin/content/blog/rubrics/' . $id . '/?error=' . rawurlencode('Preview текст должен быть не длиннее 500 символов.'));
			return;
		}

		try {
			$imagePath = $this->saveTopicImageUpload($id, 'image_file', $imagePath);

			if (!$model->updateEditorData($id, $title, $description, $imagePath, $enabled)) {
				throw new \RuntimeException('Не удалось сохранить изменения.');
			}

			header('Location: /admin/content/blog/rubrics/' . $id . '/?saved=1');
			return;
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			if ($message === '') {
				$message = 'Не удалось сохранить изменения.';
			}

			header('Location: /admin/content/blog/rubrics/' . $id . '/?error=' . rawurlencode($message));
			return;
		}
	}

	public function delete(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			if (!(new BlogTopicsModel())->deleteById($id)) {
				throw new \RuntimeException('Не удалось удалить рубрику.');
			}

			$this->setFlash(true, 'Рубрика удалена.');
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->setFlash(false, $message !== '' ? $message : 'Не удалось удалить рубрику.');
		}

		header('Location: /admin/content/blog/rubrics/');
	}

	private function saveTopicImageUpload(int $topicId, string $fileKey, string $existingUrl): string
	{
		$file = $_FILES[$fileKey] ?? null;
		if (!is_array($file)) {
			return $existingUrl;
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode === UPLOAD_ERR_NO_FILE) {
			return $existingUrl;
		}

		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new \RuntimeException('Ошибка загрузки изображения.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new \RuntimeException('Некорректный загруженный файл.');
		}

		$mime = $this->detectImageMimeType($tmpPath);
		$allowedMimeToExt = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/webp' => 'webp',
		];

		if (!isset($allowedMimeToExt[$mime])) {
			throw new \RuntimeException('Разрешены только JPG/PNG/GIF/WEBP изображения.');
		}

		$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
		if ($documentRoot === '') {
			throw new \RuntimeException('Document root is not configured.');
		}

		$uploadDir = $documentRoot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'topics';
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
			throw new \RuntimeException('Не удалось создать папку загрузки.');
		}

		$fileName = sprintf(
			'blog_topic_%d_%s_%s.%s',
			$topicId,
			date('Ymd_His'),
			bin2hex(random_bytes(4)),
			$allowedMimeToExt[$mime]
		);
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new \RuntimeException('Не удалось сохранить изображение.');
		}

		return '/upload/images/blog/topics/' . $fileName;
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

	private function ensureAdmin(): bool
	{
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			header('Location: /admin/login/');
			return false;
		}

		return true;
	}

	private function setFlash(bool $success, string $message): void
	{
		$_SESSION[self::FLASH_KEY] = [
			'success' => $success,
			'message' => $message,
		];
	}
}
