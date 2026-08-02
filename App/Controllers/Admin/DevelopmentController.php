<?php

namespace Controllers\Admin;

use App\Services\Security\CsrfService;
use Modules\DBWork\DBConnection;
use Modules\Main\App;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use PDO;
use Throwable;

class DevelopmentController extends BaseController
{
	private const FLASH_KEY = 'admin_development_sql_flash';

	public function sql(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$selectedFile = $this->sanitizeFileName((string) ($_GET['file'] ?? ''));
		$selectedSql = '';
		$loadError = '';

		if ($selectedFile !== '') {
			try {
				$selectedSql = $this->readMigrationFile($selectedFile);
			} catch (Throwable $e) {
				$loadError = $e->getMessage();
			}
		}

		$flash = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		Template::getInstance()->setParam('title', 'SQL запросы');
		Template::getInstance()->showHeader();
		$this->render('sql', [
			'files' => $this->collectMigrationFiles(),
			'selectedFile' => $selectedFile,
			'selectedSql' => $selectedSql,
			'loadError' => $loadError,
			'flash' => is_array($flash) ? $flash : null,
			'csrfToken' => (new CsrfService())->getToken(),
		]);
		Template::getInstance()->showFooter();
	}

	public function executeSql(): void
	{
		if (!$this->ensureAdmin() || !$this->validateCsrf()) {
			return;
		}

		$sql = (string) ($_POST['sql'] ?? '');
		$this->executeAndFlash($sql);
		header('Location: /admin/development/sql/');
	}

	public function executeSqlFile(): void
	{
		if (!$this->ensureAdmin() || !$this->validateCsrf()) {
			return;
		}

		$file = $this->sanitizeFileName((string) ($_POST['file'] ?? ''));
		if ($file === '') {
			$this->setFlash(false, 'Файл не выбран.');
			header('Location: /admin/development/sql/');
			return;
		}

		try {
			$this->executeAndFlash($this->readMigrationFile($file), 'Файл "' . $file . '" выполнен.');
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage());
		}

		header('Location: /admin/development/sql/');
	}

	public function deleteSqlFile(): void
	{
		if (!$this->ensureAdmin() || !$this->validateCsrf()) {
			return;
		}

		$file = $this->sanitizeFileName((string) ($_POST['file'] ?? ''));
		if ($file === '') {
			$this->setFlash(false, 'Файл не выбран.');
			header('Location: /admin/development/sql/');
			return;
		}

		try {
			$filePath = $this->resolveMigrationFilePath($file);
			if (!is_file($filePath)) {
				throw new \RuntimeException('SQL файл не найден.');
			}

			if (!@unlink($filePath)) {
				throw new \RuntimeException('Не удалось удалить SQL файл.');
			}

			$this->setFlash(true, 'Файл "' . $file . '" удален.');
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage());
		}

		header('Location: /admin/development/sql/');
	}

	private function executeAndFlash(string $sql, string $successMessage = 'SQL запрос выполнен.'): void
	{
		$sql = trim($sql);
		if ($sql === '') {
			$this->setFlash(false, 'SQL запрос не может быть пустым.', [
				'sql' => $sql,
			]);
			return;
		}

		try {
			$db = DBConnection::getConnection();
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			if ($this->isReadableQuery($sql)) {
				$stmt = $db->query($sql);
				$rows = $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

				$this->setFlash(true, $successMessage, [
					'type' => 'rows',
					'rows' => is_array($rows) ? $rows : [],
					'sql' => $sql,
				]);
				return;
			}

			$affectedRows = $db->exec($sql);
			$this->setFlash(true, $successMessage, [
				'type' => 'affected',
				'affectedRows' => $affectedRows,
				'sql' => $sql,
			]);
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage(), [
				'sql' => $sql,
			]);
		}
	}

	private function isReadableQuery(string $sql): bool
	{
		$normalized = ltrim($sql);
		$firstWord = strtoupper((string) strtok($normalized, " \t\r\n"));

		return in_array($firstWord, ['SELECT', 'SHOW', 'DESCRIBE', 'DESC', 'EXPLAIN'], true);
	}

	private function collectMigrationFiles(): array
	{
		$files = [];
		$filePaths = glob($this->getMigrationsDirectory() . '/*.sql') ?: [];
		sort($filePaths, SORT_NATURAL | SORT_FLAG_CASE);

		foreach ($filePaths as $filePath) {
			$files[] = [
				'name' => basename($filePath),
				'size' => filesize($filePath) ?: 0,
				'updated_at' => filemtime($filePath) ? date('d.m.Y H:i', (int) filemtime($filePath)) : '',
			];
		}

		return $files;
	}

	private function readMigrationFile(string $file): string
	{
		$filePath = $this->resolveMigrationFilePath($file);
		if (!is_file($filePath)) {
			throw new \RuntimeException('SQL файл не найден.');
		}

		$content = file_get_contents($filePath);
		if ($content === false) {
			throw new \RuntimeException('Не удалось прочитать SQL файл.');
		}

		return $content;
	}

	private function resolveMigrationFilePath(string $file): string
	{
		$file = $this->sanitizeFileName($file);
		if ($file === '') {
			throw new \RuntimeException('Неверное имя SQL файла.');
		}

		$directory = $this->getMigrationsDirectory();
		$directoryRealPath = realpath($directory);
		if ($directoryRealPath === false) {
			throw new \RuntimeException('Папка миграций не найдена.');
		}

		$filePath = $directoryRealPath . DIRECTORY_SEPARATOR . $file;
		$resolvedFilePath = realpath($filePath);

		if ($resolvedFilePath !== false && !str_starts_with($resolvedFilePath, $directoryRealPath . DIRECTORY_SEPARATOR)) {
			throw new \RuntimeException('Файл находится вне папки миграций.');
		}

		return $filePath;
	}

	private function sanitizeFileName(string $file): string
	{
		$file = basename(trim($file));
		if ($file === '' || !preg_match('/^[a-zA-Z0-9._-]+\.sql$/', $file)) {
			return '';
		}

		return $file;
	}

	private function getMigrationsDirectory(): string
	{
		return App::getInstance()->root . '/storage/migrations';
	}

	private function validateCsrf(): bool
	{
		if ((new CsrfService())->validate($_POST['_csrf'] ?? null)) {
			return true;
		}

		$this->setFlash(false, 'Недействительный CSRF-токен.');
		header('Location: /admin/development/sql/');
		return false;
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

	private function setFlash(bool $success, string $message, array $details = []): void
	{
		$_SESSION[self::FLASH_KEY] = [
			'success' => $success,
			'message' => $message,
			'details' => $details,
		];
	}
}
