<?php

namespace Controllers\Admin;

use Modules\Main\App;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Config;
use Modules\Main\Template;
use RuntimeException;
use Throwable;

class ConfigsController extends BaseController
{
	private const FLASH_KEY = 'admin_configs_flash';
	private const EXCLUDED_SECTIONS = ['hidden'];

	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$catalog = $this->collectConfigCatalog();
		[$selectedSection, $selectedFile] = $this->resolveSelection($catalog, $_GET);

		$configValues = null;
		$loadError = '';
		if ($selectedSection !== '' && $selectedFile !== '') {
			try {
				$configValues = $this->loadConfigFile($selectedSection, $selectedFile);
			} catch (Throwable $e) {
				$loadError = $e->getMessage();
			}
		}

		$flash = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		Template::getInstance()->setParam('title', 'Конфиги');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'catalog' => $catalog,
			'selectedSection' => $selectedSection,
			'selectedFile' => $selectedFile,
			'configValues' => $configValues,
			'loadError' => $loadError,
			'flash' => is_array($flash) ? $flash : null,
		]);
		Template::getInstance()->showFooter();
	}

	public function save(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$catalog = $this->collectConfigCatalog();
		$section = trim((string) ($_POST['section'] ?? ''));
		$file = trim((string) ($_POST['file'] ?? ''));

		if (!$this->isValidSelection($catalog, $section, $file)) {
			$this->setFlash(false, 'Неверно выбран конфигурационный файл.');
			header('Location: /admin/configs/');
			return;
		}

		$configContent = (string) ($_POST['config_content'] ?? '');
		if (trim($configContent) === '') {
			$this->setFlash(false, 'Конфигурация не может быть пустой.');
			header('Location: ' . $this->buildEditorUrl($section, $file));
			return;
		}

		try {
			$normalizedValues = $this->parseConfigContent($configContent);
			$filePath = $this->buildConfigFilePath($section, $file);
			$content = "<?php\nreturn " . $this->exportPhpValue($normalizedValues) . ";\n";

			$written = @file_put_contents($filePath, $content, LOCK_EX);
			if ($written === false) {
				throw new RuntimeException('Не удалось сохранить файл конфигурации.');
			}

			Config::getInstance()->clear();
			$this->setFlash(true, 'Конфигурация успешно сохранена.');
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage() !== '' ? $e->getMessage() : 'Ошибка при сохранении конфигурации.');
		}

		header('Location: ' . $this->buildEditorUrl($section, $file));
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

	private function collectConfigCatalog(): array
	{
		$configRoot = App::getInstance()->root . '/App/Configs';
		if (!is_dir($configRoot)) {
			return [];
		}

		$catalog = [];
		$directories = glob($configRoot . '/*', GLOB_ONLYDIR) ?: [];
		sort($directories, SORT_NATURAL | SORT_FLAG_CASE);

		foreach ($directories as $directoryPath) {
			$sectionName = basename($directoryPath);
			if ($sectionName === '') {
				continue;
			}

			if (in_array(strtolower($sectionName), self::EXCLUDED_SECTIONS, true)) {
				continue;
			}

			$files = [];
			$filePaths = glob($directoryPath . '/*.php') ?: [];
			foreach ($filePaths as $filePath) {
				$files[] = basename($filePath, '.php');
			}

			sort($files, SORT_NATURAL | SORT_FLAG_CASE);
			$catalog[$sectionName] = $files;
		}

		return $catalog;
	}

	private function resolveSelection(array $catalog, array $input): array
	{
		$selectedSection = trim((string) ($input['section'] ?? ''));
		$selectedFile = trim((string) ($input['file'] ?? ''));

		if (!isset($catalog[$selectedSection])) {
			$selectedSection = array_key_first($catalog) ?? '';
		}

		$availableFiles = $selectedSection !== '' ? ($catalog[$selectedSection] ?? []) : [];
		if ($selectedFile === '' || !in_array($selectedFile, $availableFiles, true)) {
			$selectedFile = $availableFiles[0] ?? '';
		}

		return [$selectedSection, $selectedFile];
	}

	private function isValidSelection(array $catalog, string $section, string $file): bool
	{
		if ($section === '' || $file === '') {
			return false;
		}

		if (!isset($catalog[$section])) {
			return false;
		}

		return in_array($file, $catalog[$section], true);
	}

	private function loadConfigFile(string $section, string $file): array
	{
		$filePath = $this->buildConfigFilePath($section, $file);
		if (!is_file($filePath)) {
			throw new RuntimeException('Файл конфигурации не найден.');
		}

		$config = require $filePath;
		if (!is_array($config)) {
			throw new RuntimeException('Конфигурационный файл должен возвращать массив.');
		}

		return $config;
	}

	private function buildConfigFilePath(string $section, string $file): string
	{
		return App::getInstance()->root . '/App/Configs/' . $section . '/' . $file . '.php';
	}

	private function exportPhpValue(mixed $value, int $level = 0): string
	{
		if (is_array($value)) {
			if ($value === []) {
				return '[]';
			}

			$indent = str_repeat("\t", $level);
			$childIndent = str_repeat("\t", $level + 1);
			$lines = ["["];

			foreach ($value as $key => $item) {
				$keyLiteral = is_int($key) ? (string) $key : var_export((string) $key, true);
				$lines[] = $childIndent . $keyLiteral . ' => ' . $this->exportPhpValue($item, $level + 1) . ',';
			}

			$lines[] = $indent . ']';
			return implode(PHP_EOL, $lines);
		}

		if (is_string($value)) {
			return var_export($value, true);
		}

		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}

		if ($value === null) {
			return 'null';
		}

		if (is_float($value) || is_int($value)) {
			return (string) $value;
		}

		return var_export($value, true);
	}

	private function parseConfigContent(string $content): array
	{
		$content = trim($content);
		$content = preg_replace('~^\s*<\?php\s*~', '', $content) ?? $content;
		$content = preg_replace('~^\s*return\s+~', '', $content) ?? $content;
		$content = rtrim($content);
		if (str_ends_with($content, ';')) {
			$content = substr($content, 0, -1);
		}

		$tempFile = tempnam(sys_get_temp_dir(), 'config_edit_');
		if ($tempFile === false) {
			throw new RuntimeException('Не удалось создать временный файл для проверки конфигурации.');
		}

		try {
			$written = @file_put_contents($tempFile, "<?php\nreturn " . $content . ";\n");
			if ($written === false) {
				throw new RuntimeException('Не удалось записать временный файл для проверки конфигурации.');
			}

			$result = require $tempFile;
		} finally {
			@unlink($tempFile);
		}

		if (!is_array($result)) {
			throw new RuntimeException('Конфигурация должна возвращать массив.');
		}

		return $result;
	}

	private function buildEditorUrl(string $section, string $file): string
	{
		$query = http_build_query([
			'section' => $section,
			'file' => $file,
		]);

		return '/admin/configs/' . ($query !== '' ? ('?' . $query) : '');
	}

	private function setFlash(bool $success, string $message): void
	{
		$_SESSION[self::FLASH_KEY] = [
			'success' => $success,
			'message' => $message,
		];
	}
}
