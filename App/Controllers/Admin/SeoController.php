<?php

namespace Controllers\Admin;

use App\Services\Security\CsrfService;
use App\Services\Seo\Config\SeoConfig;
use App\Services\Seo\SeoContext;
use App\Services\Seo\SeoMeta;
use App\Services\Seo\SeoService;
use App\Services\Seo\SeoValidator;
use Models\SeoMetaModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class SeoController extends BaseController
{
	private const TARGET_TYPE = 'page';

	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$config = new SeoConfig();
		$service = new SeoService();
		$model = new SeoMetaModel();
		$items = [];

		foreach ($config->getPages() as $pageKey => $pageConfig) {
			$db = $model->findByTarget(self::TARGET_TYPE, (string) $pageKey);
			$resolved = $service->resolve(SeoContext::page((string) $pageKey));

			$items[] = [
				'key' => (string) $pageKey,
				'label' => (string) ($pageConfig['label'] ?? $pageKey),
				'path' => (string) ($pageConfig['path'] ?? '/'),
				'title' => $resolved->title,
				'status' => $this->resolveStatus($resolved, $db),
			];
		}

		Template::getInstance()->setParam('title', 'SEO');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'items' => $items,
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'reset' => isset($_GET['reset']) && $_GET['reset'] === '1',
			'error' => trim((string) ($_GET['error'] ?? '')),
		]);
		Template::getInstance()->showFooter();
	}

	public function edit(string $pageKey): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$config = new SeoConfig();
		if (!$config->hasPage($pageKey)) {
			header('Location: /admin/seo/?error=' . rawurlencode('Страница не найдена в SEO-конфиге.'));
			return;
		}

		$pageConfig = $config->getPage($pageKey) ?? [];
		$db = (new SeoMetaModel())->findByTarget(self::TARGET_TYPE, $pageKey);
		$resolved = (new SeoService())->resolve(SeoContext::page($pageKey));

		Template::getInstance()->setParam('title', 'SEO — ' . (string) ($pageConfig['label'] ?? $pageKey));
		Template::getInstance()->showHeader();
		$this->render('edit', [
			'pageKey' => $pageKey,
			'pageConfig' => $pageConfig,
			'record' => $db,
			'resolved' => $resolved,
			'csrfToken' => (new CsrfService())->getToken(),
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'error' => trim((string) ($_GET['error'] ?? '')),
		]);
		Template::getInstance()->showFooter();
	}

	public function update(string $pageKey): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$config = new SeoConfig();
		if (!$config->hasPage($pageKey)) {
			header('Location: /admin/seo/?error=' . rawurlencode('Страница не найдена в SEO-конфиге.'));
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['csrf_token'] ?? ''))) {
			header('Location: /admin/seo/' . rawurlencode($pageKey) . '/?error=' . rawurlencode('Недействительный CSRF-токен.'));
			return;
		}

		try {
			$data = (new SeoValidator())->validatePageForm($_POST);
			$existingImage = trim((string) ($_POST['og_image_existing'] ?? ''));
			$data['og_image'] = $this->saveOgImageUpload('og_image_file', $data['og_image'] ?? $existingImage);

			if (!(new SeoMetaModel())->saveByTarget(self::TARGET_TYPE, $pageKey, $data)) {
				throw new \RuntimeException('Не удалось сохранить SEO-настройки.');
			}

			header('Location: /admin/seo/' . rawurlencode($pageKey) . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/seo/' . rawurlencode($pageKey) . '/', $exception);
		}
	}

	public function reset(string $pageKey): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$config = new SeoConfig();
		if (!$config->hasPage($pageKey)) {
			header('Location: /admin/seo/?error=' . rawurlencode('Страница не найдена в SEO-конфиге.'));
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['csrf_token'] ?? ''))) {
			header('Location: /admin/seo/' . rawurlencode($pageKey) . '/?error=' . rawurlencode('Недействительный CSRF-токен.'));
			return;
		}

		try {
			(new SeoMetaModel())->deleteByTarget(self::TARGET_TYPE, $pageKey);
			header('Location: /admin/seo/?reset=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/seo/' . rawurlencode($pageKey) . '/', $exception);
		}
	}

	private function resolveStatus(SeoMeta $resolved, ?object $db): string
	{
		if ($db !== null && (int) ($db->robots_index ?? 1) === 0) {
			return 'Запрещена индексация';
		}

		if ($resolved->description === '') {
			return 'Не заполнено описание';
		}

		if ($this->hasManualSettings($db)) {
			return 'Настроено';
		}

		return 'Используются значения по умолчанию';
	}

	private function hasManualSettings(?object $db): bool
	{
		if ($db === null) {
			return false;
		}

		foreach (['title', 'description', 'canonical_url', 'og_title', 'og_description', 'og_image'] as $field) {
			if (trim((string) ($db->{$field} ?? '')) !== '') {
				return true;
			}
		}

		return (int) ($db->robots_index ?? 1) === 0 || (int) ($db->robots_follow ?? 1) === 0;
	}

	private function saveOgImageUpload(string $fileKey, ?string $existingUrl): ?string
	{
		$existingUrl = trim((string) $existingUrl);
		if (!isset($_FILES[$fileKey]) || !is_array($_FILES[$fileKey])) {
			return $existingUrl !== '' ? $existingUrl : null;
		}

		$file = $_FILES[$fileKey];
		$error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($error === UPLOAD_ERR_NO_FILE) {
			return $existingUrl !== '' ? $existingUrl : null;
		}

		if ($error !== UPLOAD_ERR_OK) {
			throw new \RuntimeException('Не удалось загрузить OG-изображение.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new \RuntimeException('Некорректный загруженный файл.');
		}

		$mime = (string) (mime_content_type($tmpPath) ?: '');
		$extension = match ($mime) {
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/webp' => 'webp',
			'image/gif' => 'gif',
			default => throw new \RuntimeException('Допустимы только JPG, PNG, WEBP и GIF.'),
		};

		$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR);
		$uploadDir = $documentRoot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'seo';
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
			throw new \RuntimeException('Не удалось создать директорию для загрузки.');
		}

		$fileName = 'og-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new \RuntimeException('Не удалось сохранить OG-изображение.');
		}

		return '/upload/images/seo/' . $fileName;
	}

	private function redirectWithError(string $path, Throwable $exception): void
	{
		$message = $exception instanceof \InvalidArgumentException
			? trim($exception->getMessage())
			: 'Не удалось выполнить операцию.';
		header('Location: ' . $path . '?error=' . rawurlencode($message));
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
}
