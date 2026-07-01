<?php

namespace App\Services\Backup;

use Modules\DBWork\DBConnection;
use Modules\Main\App;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

class BackupService
{
	private const DB_DUMP_FILE = '__database.sql';

	private string $root;
	private string $backupDir;

	public function __construct()
	{
		$this->root = App::getInstance()->root;
		$this->backupDir = $this->root . '/App/Backup';
	}

	public function create(bool $includeDatabase, array $excludedPaths): string
	{
		$this->ensureBackupDir();

		if (!class_exists(ZipArchive::class)) {
			throw new RuntimeException('Расширение ZipArchive недоступно.');
		}

		$fileName = date('Y-m-d-H-i-s') . '.zip';
		$filePath = $this->backupDir . '/' . $fileName;
		$zip = new ZipArchive();

		if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
			throw new RuntimeException('Не удалось создать zip-архив.');
		}

		$excludedPaths = $this->normalizeExcludedPaths($excludedPaths);
		$this->addProjectFiles($zip, $excludedPaths);

		if ($includeDatabase) {
			$zip->addFromString(self::DB_DUMP_FILE, $this->buildDatabaseDump());
		}

		$zip->close();

		return $fileName;
	}

	public function list(): array
	{
		$this->ensureBackupDir();

		$files = glob($this->backupDir . '/*.zip') ?: [];
		usort($files, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));

		$result = [];
		foreach ($files as $filePath) {
			$result[] = [
				'name' => basename($filePath),
				'path' => $filePath,
				'created_at' => date('d.m.Y H:i:s', (int) filemtime($filePath)),
				'size' => $this->formatSize((int) filesize($filePath)),
			];
		}

		return $result;
	}

	public function getBackupPath(string $fileName): string
	{
		$fileName = basename(rawurldecode($fileName));
		$filePath = $this->backupDir . '/' . $fileName;

		if ($fileName === '' || !is_file($filePath)) {
			throw new RuntimeException('Резервная копия не найдена.');
		}

		return $filePath;
	}

	public function delete(string $fileName): void
	{
		$filePath = $this->getBackupPath($fileName);
		if (!unlink($filePath)) {
			throw new RuntimeException('Не удалось удалить резервную копию.');
		}
	}

	public function upload(array $file): string
	{
		$this->ensureBackupDir();

		if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
			throw new RuntimeException('Не удалось загрузить резервную копию.');
		}

		$originalName = basename((string) ($file['name'] ?? ''));
		if ($originalName === '' || strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'zip') {
			throw new RuntimeException('Можно загрузить только zip-архив.');
		}

		$tempPath = (string) ($file['tmp_name'] ?? '');
		$this->assertValidZip($tempPath);

		$fileName = $this->buildUploadedBackupName($originalName);
		$targetPath = $this->backupDir . '/' . $fileName;

		if (!move_uploaded_file($tempPath, $targetPath)) {
			throw new RuntimeException('Не удалось сохранить загруженную копию.');
		}

		return $fileName;
	}

	public function restore(string $fileName, string $mode): void
	{
		$filePath = $this->getBackupPath($fileName);
		$mode = in_array($mode, ['overlay', 'exact', 'missing'], true) ? $mode : 'overlay';
		$tempDir = sys_get_temp_dir() . '/lancy_backup_restore_' . bin2hex(random_bytes(6));

		if (!mkdir($tempDir, 0775, true) && !is_dir($tempDir)) {
			throw new RuntimeException('Не удалось создать временную папку восстановления.');
		}

		try {
			$this->extractBackup($filePath, $tempDir);

			if (is_file($tempDir . '/' . self::DB_DUMP_FILE)) {
				$this->restoreDatabase($tempDir . '/' . self::DB_DUMP_FILE);
				unlink($tempDir . '/' . self::DB_DUMP_FILE);
			}

			if ($mode === 'exact') {
				$this->clearProjectBeforeRestore();
			}

			$this->copyRestoredFiles($tempDir, $this->root, $mode);
		} finally {
			$this->removeDirectory($tempDir);
		}
	}

	private function addProjectFiles(ZipArchive $zip, array $excludedPaths): void
	{
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $item) {
			$path = str_replace('\\', '/', $item->getPathname());
			$relativePath = ltrim(str_replace(str_replace('\\', '/', $this->root), '', $path), '/');

			if ($relativePath === '' || $this->isExcluded($relativePath, $excludedPaths)) {
				continue;
			}

			if ($item->isDir()) {
				$zip->addEmptyDir($relativePath);
				continue;
			}

			if ($item->isFile()) {
				$zip->addFile($item->getPathname(), $relativePath);
			}
		}
	}

	private function normalizeExcludedPaths(array $paths): array
	{
		$default = [
			'.git',
			'App/Backup',
		];

		$paths = array_merge($default, $paths);
		$result = [];

		foreach ($paths as $path) {
			$path = trim(str_replace('\\', '/', (string) $path), " \t\n\r\0\x0B/");
			if ($path !== '') {
				$result[] = $path;
			}
		}

		return array_values(array_unique($result));
	}

	private function isExcluded(string $relativePath, array $excludedPaths): bool
	{
		$relativePath = trim(str_replace('\\', '/', $relativePath), '/');
		foreach ($excludedPaths as $excludedPath) {
			if ($relativePath === $excludedPath || str_starts_with($relativePath, $excludedPath . '/')) {
				return true;
			}
		}

		return false;
	}

	private function buildDatabaseDump(): string
	{
		$db = DBConnection::getConnection();
		$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
		$dump = "-- Lancy backup\nSET FOREIGN_KEY_CHECKS=0;\n\n";

		foreach ($tables as $table) {
			$table = (string) $table;
			$create = $db->query('SHOW CREATE TABLE `' . str_replace('`', '``', $table) . '`')->fetch(PDO::FETCH_ASSOC);
			$createSql = (string) ($create['Create Table'] ?? '');

			$dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
			$dump .= $createSql . ";\n\n";

			$rows = $db->query('SELECT * FROM `' . str_replace('`', '``', $table) . '`')->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$columns = array_map(static fn(string $column): string => '`' . str_replace('`', '``', $column) . '`', array_keys($row));
				$values = array_map(
					static fn($value): string => $value === null ? 'NULL' : $db->quote((string) $value),
					array_values($row)
				);

				$dump .= 'INSERT INTO `' . str_replace('`', '``', $table) . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n";
			}

			$dump .= "\n";
		}

		return $dump . "SET FOREIGN_KEY_CHECKS=1;\n";
	}

	private function extractBackup(string $filePath, string $targetDir): void
	{
		$zip = new ZipArchive();
		if ($zip->open($filePath) !== true) {
			throw new RuntimeException('Не удалось открыть резервную копию.');
		}

		for ($i = 0; $i < $zip->numFiles; $i++) {
			$name = (string) $zip->getNameIndex($i);
			if ($name === '' || str_starts_with($name, '/') || str_contains($name, '..')) {
				$zip->close();
				throw new RuntimeException('Архив содержит небезопасный путь.');
			}
		}

		if (!$zip->extractTo($targetDir)) {
			$zip->close();
			throw new RuntimeException('Не удалось распаковать резервную копию.');
		}

		$zip->close();
	}

	private function assertValidZip(string $filePath): void
	{
		$zip = new ZipArchive();
		if ($filePath === '' || $zip->open($filePath) !== true) {
			throw new RuntimeException('Загруженный файл не является корректным zip-архивом.');
		}

		$zip->close();
	}

	private function buildUploadedBackupName(string $originalName): string
	{
		$baseName = pathinfo($originalName, PATHINFO_FILENAME);
		$baseName = preg_replace('~[^a-zA-Z0-9._-]+~', '-', $baseName) ?: 'uploaded-backup';
		$baseName = trim($baseName, '.-_') ?: 'uploaded-backup';
		$fileName = $baseName . '.zip';

		if (!is_file($this->backupDir . '/' . $fileName)) {
			return $fileName;
		}

		return $baseName . '-' . date('Y-m-d-H-i-s') . '.zip';
	}

	private function restoreDatabase(string $dumpPath): void
	{
		$sql = file_get_contents($dumpPath);
		if ($sql === false || trim($sql) === '') {
			return;
		}

		$db = DBConnection::getConnection();
		$statements = array_filter(array_map('trim', explode(";\n", $sql)));
		foreach ($statements as $statement) {
			$db->exec($statement);
		}
	}

	private function clearProjectBeforeRestore(): void
	{
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			$path = str_replace('\\', '/', $item->getPathname());
			$relativePath = ltrim(str_replace(str_replace('\\', '/', $this->root), '', $path), '/');

			if ($this->isExcluded($relativePath, ['.git', 'App/Backup'])) {
				continue;
			}

			$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
		}
	}

	private function copyRestoredFiles(string $sourceDir, string $targetDir, string $mode): void
	{
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $item) {
			$relativePath = ltrim(str_replace(str_replace('\\', '/', $sourceDir), '', str_replace('\\', '/', $item->getPathname())), '/');
			$targetPath = $targetDir . '/' . $relativePath;

			if ($item->isDir()) {
				if (!is_dir($targetPath) && !mkdir($targetPath, 0775, true) && !is_dir($targetPath)) {
					throw new RuntimeException('Не удалось создать папку при восстановлении.');
				}
				continue;
			}

			if ($mode === 'missing' && is_file($targetPath)) {
				continue;
			}

			$targetParent = dirname($targetPath);
			if (!is_dir($targetParent) && !mkdir($targetParent, 0775, true) && !is_dir($targetParent)) {
				throw new RuntimeException('Не удалось создать папку для файла при восстановлении.');
			}

			if (!copy($item->getPathname(), $targetPath)) {
				throw new RuntimeException('Не удалось восстановить файл: ' . $relativePath);
			}
		}
	}

	private function ensureBackupDir(): void
	{
		if (!is_dir($this->backupDir) && !mkdir($this->backupDir, 0775, true) && !is_dir($this->backupDir)) {
			throw new RuntimeException('Не удалось создать папку резервных копий.');
		}
	}

	private function removeDirectory(string $directory): void
	{
		if (!is_dir($directory)) {
			return;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
		}

		@rmdir($directory);
	}

	private function formatSize(int $bytes): string
	{
		$units = ['Б', 'КБ', 'МБ', 'ГБ'];
		$size = (float) $bytes;
		$unitIndex = 0;

		while ($size >= 1024 && $unitIndex < count($units) - 1) {
			$size /= 1024;
			$unitIndex++;
		}

		return round($size, $unitIndex === 0 ? 0 : 2) . ' ' . $units[$unitIndex];
	}
}
