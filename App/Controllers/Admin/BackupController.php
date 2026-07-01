<?php

namespace Controllers\Admin;

use App\Services\Backup\BackupService;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class BackupController extends BaseController
{
	private const FLASH_KEY = 'admin_backup_flash';

	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Резервное копирование');
		Template::getInstance()->showHeader();
		$this->render('index');
		Template::getInstance()->showFooter();
	}

	public function create(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Создание резервной копии');
		Template::getInstance()->showHeader();
		$this->render('create', [
			'flash' => $this->pullFlash(),
		]);
		Template::getInstance()->showFooter();
	}

	public function store(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$includeDatabase = (int) ($_POST['include_database'] ?? 0) === 1;
		$excludedPaths = $this->parseExcludedPaths((string) ($_POST['excluded_paths'] ?? ''));

		try {
			$fileName = (new BackupService())->create($includeDatabase, $excludedPaths);
			$this->setFlash(true, 'Резервная копия создана: ' . $fileName);
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage() !== '' ? $e->getMessage() : 'Не удалось создать резервную копию.');
		}

		header('Location: /admin/settings/backup/create/');
	}

	public function list(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Список резервных копий');
		Template::getInstance()->showHeader();
		$this->render('list', [
			'backups' => (new BackupService())->list(),
			'flash' => $this->pullFlash(),
		]);
		Template::getInstance()->showFooter();
	}

	public function delete(string $file): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			(new BackupService())->delete($file);
			$this->setFlash(true, 'Резервная копия удалена.');
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage() !== '' ? $e->getMessage() : 'Не удалось удалить резервную копию.');
		}

		header('Location: /admin/settings/backup/list/');
	}

	public function upload(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$fileName = (new BackupService())->upload($_FILES['backup_file'] ?? []);
			$this->setFlash(true, 'Резервная копия загружена: ' . $fileName);
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage() !== '' ? $e->getMessage() : 'Не удалось загрузить резервную копию.');
		}

		header('Location: /admin/settings/backup/list/');
	}

	public function restore(string $file): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$mode = (string) ($_POST['mode'] ?? 'overlay');

		try {
			(new BackupService())->restore($file, $mode);
			$this->setFlash(true, 'Резервная копия восстановлена.');
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage() !== '' ? $e->getMessage() : 'Не удалось восстановить резервную копию.');
		}

		header('Location: /admin/settings/backup/list/');
	}

	public function download(string $file): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$filePath = (new BackupService())->getBackupPath($file);
		} catch (Throwable) {
			http_response_code(404);
			echo 'Резервная копия не найдена.';
			return;
		}

		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
		header('Content-Length: ' . filesize($filePath));
		readfile($filePath);
		exit;
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

	private function parseExcludedPaths(string $rawPaths): array
	{
		return array_values(array_filter(array_map('trim', preg_split('~\R~', $rawPaths) ?: [])));
	}

	private function setFlash(bool $success, string $message): void
	{
		$_SESSION[self::FLASH_KEY] = [
			'success' => $success,
			'message' => $message,
		];
	}

	private function pullFlash(): ?array
	{
		$flash = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		return is_array($flash) ? $flash : null;
	}
}
