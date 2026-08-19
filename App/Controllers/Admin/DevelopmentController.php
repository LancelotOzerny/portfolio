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

	public function downloadSqlFile(string $file): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$fileName = $this->sanitizeFileName($file);
			if ($fileName === '') {
				throw new \RuntimeException('Неверное имя SQL файла.');
			}

			$filePath = $this->resolveMigrationFilePath($fileName);
			if (!is_file($filePath)) {
				throw new \RuntimeException('SQL файл не найден.');
			}

			header('Content-Type: application/sql; charset=utf-8');
			header('Content-Disposition: attachment; filename="' . $fileName . '"');
			header('Content-Length: ' . (string) filesize($filePath));
			readfile($filePath);
			exit;
		} catch (Throwable $e) {
			http_response_code(404);
			echo htmlspecialchars($e->getMessage());
		}
	}

	public function uploadSqlFile(): void
	{
		if (!$this->ensureAdmin() || !$this->validateCsrf()) {
			return;
		}

		try {
			$fileName = $this->storeUploadedMigration($_FILES['sql_file'] ?? []);
			$this->setFlash(true, 'Файл "' . $fileName . '" загружен.');
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

			$statements = $this->splitSqlStatements($sql);
			if ($statements === []) {
				$this->setFlash(false, 'SQL запрос не может быть пустым.', [
					'sql' => $sql,
				]);
				return;
			}

			if (count($statements) === 1 && $this->isReadableQuery($statements[0])) {
				$stmt = $db->query($statements[0]);
				$rows = $stmt !== false ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

				$this->setFlash(true, $successMessage, [
					'type' => 'rows',
					'rows' => is_array($rows) ? $rows : [],
					'sql' => $sql,
				]);
				return;
			}

			$affectedRows = 0;
			foreach ($statements as $statement) {
				$result = $db->exec($statement);
				$affectedRows += is_int($result) ? $result : 0;
			}

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

	/**
	 * @return list<string>
	 */
	private function splitSqlStatements(string $sql): array
	{
		$withoutComments = preg_replace('/^\s*--[^\n]*$/m', '', $sql) ?? $sql;
		$parts = explode(';', $withoutComments);
		$statements = [];

		foreach ($parts as $part) {
			$statement = trim($part);
			if ($statement !== '') {
				$statements[] = $statement;
			}
		}

		return $statements;
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

	private function storeUploadedMigration(array $file): string
	{
		if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
			throw new \RuntimeException('Не удалось загрузить SQL файл.');
		}

		$originalName = basename((string) ($file['name'] ?? ''));
		$fileName = $this->sanitizeFileName($originalName);
		if ($fileName === '') {
			throw new \RuntimeException('Можно загрузить только файл с именем вида name.sql.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new \RuntimeException('Некорректный загруженный файл.');
		}

		$extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
		if ($extension !== 'sql') {
			throw new \RuntimeException('Можно загрузить только SQL файл.');
		}

		$directory = $this->ensureMigrationsDirectory();
		$targetPath = $directory . DIRECTORY_SEPARATOR . $fileName;

		if (is_file($targetPath)) {
			throw new \RuntimeException('Файл "' . $fileName . '" уже существует.');
		}

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new \RuntimeException('Не удалось сохранить SQL файл.');
		}

		return $fileName;
	}

	private function ensureMigrationsDirectory(): string
	{
		$directory = $this->getMigrationsDirectory();
		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
			throw new \RuntimeException('Не удалось создать папку миграций.');
		}

		$directoryRealPath = realpath($directory);
		if ($directoryRealPath === false) {
			throw new \RuntimeException('Папка миграций не найдена.');
		}

		return $directoryRealPath;
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
