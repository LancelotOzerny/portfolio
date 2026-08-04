<?php

namespace App\Services\Component;

use Modules\Main\App;

class ComponentSettingsStorage
{
	private static function getFilePath(): string
	{
		return App::getInstance()->root . '/App/Data/component-settings.json';
	}

	public static function get(string $key): array
	{
		$all = self::readAll();
		$stored = $all[$key] ?? [];

		return is_array($stored) ? $stored : [];
	}

	public static function set(string $key, array $params): void
	{
		$all = self::readAll();
		$all[$key] = $params;
		self::writeAll($all);
	}

	private static function readAll(): array
	{
		$filePath = self::getFilePath();
		if (!is_file($filePath)) {
			return [];
		}

		$data = json_decode((string) file_get_contents($filePath), true);

		return is_array($data) ? $data : [];
	}

	private static function writeAll(array $data): void
	{
		$filePath = self::getFilePath();
		$directory = dirname($filePath);

		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
			throw new \RuntimeException('Не удалось создать папку настроек компонентов.');
		}

		$encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		if ($encoded === false || file_put_contents($filePath, $encoded, LOCK_EX) === false) {
			throw new \RuntimeException('Не удалось сохранить настройки компонента.');
		}
	}
}
