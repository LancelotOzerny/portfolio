<?php

namespace Controllers\Admin;

use App\Services\Gallery\GalleryStorageService;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class GalleryController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$albums = [];
		$error = trim((string) ($_GET['error'] ?? ''));

		try {
			$albums = (new GalleryStorageService())->getAlbums();
		} catch (Throwable) {
			$error = 'Не удалось загрузить фотогалерею.';
		}

		Template::getInstance()->setParam('title', 'Контент — галерея');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'albums' => $albums,
			'uploaded' => isset($_GET['uploaded']) && $_GET['uploaded'] === '1',
			'deleted' => isset($_GET['deleted']) && $_GET['deleted'] === '1',
			'created' => isset($_GET['created']) && $_GET['created'] === '1',
			'error' => $error,
		]);
		Template::getInstance()->showFooter();
	}

	public function createAlbum(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$name = trim((string) ($_POST['name'] ?? ''));
			(new GalleryStorageService())->createAlbum($name);
			header('Location: /admin/content/gallery/?created=1');
		} catch (Throwable $exception) {
			$this->redirectWithError($exception);
		}
	}

	public function upload(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$album = trim((string) ($_POST['album'] ?? ''));
			(new GalleryStorageService())->uploadPhoto($album, $_FILES['photo'] ?? []);
			header('Location: /admin/content/gallery/?uploaded=1');
		} catch (Throwable $exception) {
			$this->redirectWithError($exception);
		}
	}

	public function delete(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$album = trim((string) ($_POST['album'] ?? ''));
			$filename = trim((string) ($_POST['filename'] ?? ''));
			(new GalleryStorageService())->deletePhoto($album, $filename);
			header('Location: /admin/content/gallery/?deleted=1');
		} catch (Throwable $exception) {
			$this->redirectWithError($exception);
		}
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

	private function redirectWithError(Throwable $exception): void
	{
		$message = trim($exception->getMessage());
		if ($message === '') {
			$message = 'Не удалось выполнить операцию.';
		}

		header('Location: /admin/content/gallery/?error=' . rawurlencode($message));
	}
}
