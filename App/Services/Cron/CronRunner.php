<?php

namespace App\Services\Cron;

use DateTimeImmutable;
use Modules\Main\App;
use Throwable;

final class CronRunner
{
	public function run(?DateTimeImmutable $time = null): int
	{
		$time ??= new DateTimeImmutable('now');
		$matcher = new CronScheduleMatcher();
		$storage = new CronTaskStorage();
		$executed = 0;

		foreach ($storage->getAll() as $task) {
			if (empty($task['enabled'])) {
				continue;
			}

			$schedule = is_array($task['schedule'] ?? null) ? $task['schedule'] : [];
			if (!$matcher->matches($schedule, $time)) {
				continue;
			}

			$this->executeTask($task);
			$executed++;
		}

		$this->log(sprintf('[%s] Выполнено задач: %d', $time->format('Y-m-d H:i:s'), $executed));

		return 0;
	}

	private function executeTask(array $task): void
	{
		$class = trim((string) ($task['class'] ?? ''));
		$method = trim((string) ($task['method'] ?? ''));
		$name = trim((string) ($task['name'] ?? ''));
		$id = (int) ($task['id'] ?? 0);

		if ($class === '' || $method === '') {
			$this->log(sprintf('[task:%d] Пропуск "%s": не указан класс или метод.', $id, $name));

			return;
		}

		if (!class_exists($class)) {
			$this->log(sprintf('[task:%d] Класс не найден: %s', $id, $class));

			return;
		}

		try {
			$instance = new $class();
		} catch (Throwable $exception) {
			$this->log(sprintf('[task:%d] Не удалось создать класс %s: %s', $id, $class, $exception->getMessage()));

			return;
		}

		if (!method_exists($instance, $method)) {
			$this->log(sprintf('[task:%d] Метод не найден: %s::%s', $id, $class, $method));

			return;
		}

		$reflection = new \ReflectionMethod($instance, $method);
		if (!$reflection->isPublic()) {
			$this->log(sprintf('[task:%d] Метод не public: %s::%s', $id, $class, $method));

			return;
		}

		$params = $this->parseParams((string) ($task['params'] ?? ''));

		try {
			$reflection->invokeArgs($instance, $params);
			$this->log(sprintf('[task:%d] Выполнена задача "%s" (%s::%s)', $id, $name, $class, $method));
		} catch (Throwable $exception) {
			$this->log(sprintf(
				'[task:%d] Ошибка "%s" (%s::%s): %s',
				$id,
				$name,
				$class,
				$method,
				$exception->getMessage()
			));
		}
	}

	private function parseParams(string $params): array
	{
		$params = trim($params);
		if ($params === '') {
			return [];
		}

		return preg_split('/\s+/', $params) ?: [];
	}

	private function log(string $message): void
	{
		$line = $message . PHP_EOL;
		$logFile = App::getInstance()->root . '/App/Data/cron.log';

		if (PHP_SAPI === 'cli') {
			echo $line;
		}

		file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
	}
}
