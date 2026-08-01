<?php

namespace App\Services\Templates;

use Modules\Main\App;
use RuntimeException;

class TemplateCatalogService
{
	private const INFO_FILE = 'template_info.php';
	private const LOGO_FILES = ['template_logo.png', 'template_logo.jpg'];
	private const SYSTEM_DIRECTORIES = ['Shared'];

	private string $templatesRoot;

	public function __construct()
	{
		$this->templatesRoot = App::getInstance()->root . '/public_html/Templates';
	}

	public function list(): array
	{
		if (!is_dir($this->templatesRoot)) {
			return [];
		}

		$activeTemplates = $this->getActiveTemplateCodes();
		$directories = glob($this->templatesRoot . '/*', GLOB_ONLYDIR) ?: [];
		sort($directories, SORT_NATURAL | SORT_FLAG_CASE);

		$templates = [];
		foreach ($directories as $directory) {
			$code = basename($directory);
			if ($code === '') {
				continue;
			}

			$info = $this->loadTemplateInfo($directory);
			$isSystem = in_array($code, self::SYSTEM_DIRECTORIES, true);
			$isActive = in_array($code, $activeTemplates, true);

			$templates[] = [
				'code' => $code,
				'name' => $info['name'] !== '' ? $info['name'] : $code,
				'description' => $info['description'],
				'logo' => $this->findLogoUrl($code, $directory),
				'is_active' => $isActive,
				'is_system' => $isSystem,
				'can_delete' => !$isActive && !$isSystem,
			];
		}

		return $templates;
	}

	public function create(string $code, string $name, string $description): void
	{
		$code = $this->normalizeCode($code);
		$name = trim($name);
		$description = trim($description);

		if ($code === '') {
			throw new RuntimeException('Укажите код папки шаблона.');
		}

		if ($name === '') {
			throw new RuntimeException('Укажите название шаблона.');
		}

		$directory = $this->templatesRoot . '/' . $code;
		if (is_dir($directory)) {
			throw new RuntimeException('Шаблон с таким кодом уже существует.');
		}

		if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
			throw new RuntimeException('Не удалось создать папку шаблона.');
		}

		$content = "<?php\nreturn " . $this->exportPhpValue([
			'name' => $name,
			'description' => $description,
		]) . ";\n";

		if (file_put_contents($directory . '/' . self::INFO_FILE, $content, LOCK_EX) === false) {
			throw new RuntimeException('Не удалось сохранить информацию о шаблоне.');
		}
	}

	public function delete(string $code): void
	{
		$code = $this->normalizeCode(rawurldecode($code));
		if ($code === '') {
			throw new RuntimeException('Не выбран шаблон для удаления.');
		}

		if (in_array($code, self::SYSTEM_DIRECTORIES, true)) {
			throw new RuntimeException('Системный шаблон нельзя удалить.');
		}

		if (in_array($code, $this->getActiveTemplateCodes(), true)) {
			throw new RuntimeException('Нельзя удалить шаблон, который используется в конфигурации сайта.');
		}

		$directory = $this->templatesRoot . '/' . $code;
		$this->assertTemplateDirectory($directory);
		$this->removeDirectory($directory);
	}

	private function loadTemplateInfo(string $directory): array
	{
		$infoPath = $directory . '/' . self::INFO_FILE;
		if (!is_file($infoPath)) {
			return [
				'name' => '',
				'description' => '',
			];
		}

		$info = require $infoPath;
		if (!is_array($info)) {
			return [
				'name' => '',
				'description' => '',
			];
		}

		return [
			'name' => trim((string) ($info['name'] ?? '')),
			'description' => trim((string) ($info['description'] ?? '')),
		];
	}

	private function findLogoUrl(string $code, string $directory): string
	{
		foreach (self::LOGO_FILES as $fileName) {
			if (is_file($directory . '/' . $fileName)) {
				return '/Templates/' . rawurlencode($code) . '/' . $fileName;
			}
		}

		return '';
	}

	private function getActiveTemplateCodes(): array
	{
		$configPath = App::getInstance()->root . '/App/Configs/App/templates.php';
		if (!is_file($configPath)) {
			return [];
		}

		$config = require $configPath;
		if (!is_array($config)) {
			return [];
		}

		$codes = [];
		foreach ($config as $rule) {
			if (is_array($rule) && isset($rule['template'])) {
				$codes[] = (string) $rule['template'];
			}
		}

		return array_values(array_unique(array_filter($codes)));
	}

	private function normalizeCode(string $code): string
	{
		$code = trim($code);
		if (!preg_match('~^[a-zA-Z0-9_-]+$~', $code)) {
			return '';
		}

		return $code;
	}

	private function assertTemplateDirectory(string $directory): void
	{
		$root = realpath($this->templatesRoot);
		$target = realpath($directory);

		if ($root === false || $target === false || !is_dir($target)) {
			throw new RuntimeException('Шаблон не найден.');
		}

		$root = rtrim(str_replace('\\', '/', $root), '/') . '/';
		$target = rtrim(str_replace('\\', '/', $target), '/') . '/';

		if (!str_starts_with($target, $root)) {
			throw new RuntimeException('Некорректный путь шаблона.');
		}
	}

	private function removeDirectory(string $directory): void
	{
		$items = scandir($directory);
		if ($items === false) {
			throw new RuntimeException('Не удалось прочитать папку шаблона.');
		}

		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}

			$path = $directory . '/' . $item;
			if (is_dir($path)) {
				$this->removeDirectory($path);
				continue;
			}

			if (!unlink($path)) {
				throw new RuntimeException('Не удалось удалить файл шаблона.');
			}
		}

		if (!rmdir($directory)) {
			throw new RuntimeException('Не удалось удалить папку шаблона.');
		}
	}

	private function exportPhpValue(array $value): string
	{
		$lines = ['['];
		foreach ($value as $key => $item) {
			$lines[] = "\t" . var_export((string) $key, true) . ' => ' . var_export((string) $item, true) . ',';
		}
		$lines[] = ']';

		return implode(PHP_EOL, $lines);
	}
}
