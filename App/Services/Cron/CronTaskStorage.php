<?php

namespace App\Services\Cron;

use Modules\Main\App;
use RuntimeException;

final class CronTaskStorage
{
	private static function getFilePath(): string
	{
		return App::getInstance()->root . '/App/Data/cron-tasks.json';
	}

	public function getAll(): array
	{
		$tasks = self::readAll();

		usort($tasks, static fn(array $a, array $b): int => ($a['id'] ?? 0) <=> ($b['id'] ?? 0));

		return $tasks;
	}

	public function findById(int $id): ?array
	{
		foreach ($this->getAll() as $task) {
			if ((int) ($task['id'] ?? 0) === $id) {
				return $task;
			}
		}

		return null;
	}

	public function save(array $task): array
	{
		$tasks = self::readAll();
		$normalized = $this->normalizeTask($task);

		$id = (int) ($normalized['id'] ?? 0);
		if ($id <= 0) {
			$normalized['id'] = $this->getNextId($tasks);
			$tasks[] = $normalized;
		} else {
			$updated = false;
			foreach ($tasks as $index => $existing) {
				if ((int) ($existing['id'] ?? 0) === $id) {
					$tasks[$index] = $normalized;
					$updated = true;
					break;
				}
			}

			if (!$updated) {
				$tasks[] = $normalized;
			}
		}

		self::writeAll($tasks);

		return $normalized;
	}

	public function delete(int $id): void
	{
		$tasks = array_values(array_filter(
			self::readAll(),
			static fn(array $task): bool => (int) ($task['id'] ?? 0) !== $id
		));

		self::writeAll($tasks);
	}

	private function getNextId(array $tasks): int
	{
		$maxId = 0;
		foreach ($tasks as $task) {
			$maxId = max($maxId, (int) ($task['id'] ?? 0));
		}

		return $maxId + 1;
	}

	private function normalizeTask(array $task): array
	{
		return [
			'id' => (int) ($task['id'] ?? 0),
			'name' => trim((string) ($task['name'] ?? '')),
			'description' => trim((string) ($task['description'] ?? '')),
			'class' => trim((string) ($task['class'] ?? '')),
			'method' => trim((string) ($task['method'] ?? '')),
			'params' => trim((string) ($task['params'] ?? '')),
			'schedule' => [
				'minute' => trim((string) ($task['schedule']['minute'] ?? '*')) ?: '*',
				'hour' => trim((string) ($task['schedule']['hour'] ?? '*')) ?: '*',
				'day' => trim((string) ($task['schedule']['day'] ?? '*')) ?: '*',
				'month' => trim((string) ($task['schedule']['month'] ?? '*')) ?: '*',
				'weekday' => trim((string) ($task['schedule']['weekday'] ?? '*')) ?: '*',
			],
			'enabled' => !empty($task['enabled']),
		];
	}

	private static function readAll(): array
	{
		$filePath = self::getFilePath();
		if (!is_file($filePath)) {
			return [];
		}

		$data = json_decode((string) file_get_contents($filePath), true);
		if (!is_array($data)) {
			return [];
		}

		return array_values(array_filter($data, 'is_array'));
	}

	private static function writeAll(array $tasks): void
	{
		$filePath = self::getFilePath();
		$directory = dirname($filePath);

		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
			throw new RuntimeException('Не удалось создать папку cron-задач.');
		}

		$encoded = json_encode(array_values($tasks), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		if ($encoded === false || file_put_contents($filePath, $encoded, LOCK_EX) === false) {
			throw new RuntimeException('Не удалось сохранить cron-задачи.');
		}
	}
}
