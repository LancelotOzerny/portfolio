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
		$queue = [];

		foreach ($storage->getAll() as $task) {
			if (empty($task['enabled'])) {
				continue;
			}

			$schedule = is_array($task['schedule'] ?? null) ? $task['schedule'] : [];
			if (!$matcher->matches($schedule, $time)) {
				continue;
			}

			$subtasks = is_array($task['subtasks'] ?? null) ? $task['subtasks'] : [];
			if ($subtasks !== []) {
				foreach ($subtasks as $subtask) {
					if (empty($subtask['enabled'])) {
						continue;
					}

					$queue[] = $this->buildRunnableItem($task, $subtask, true);
				}
				continue;
			}

			$queue[] = $this->buildRunnableItem($task, $task, false);
		}

		$queue = CronTaskPriority::sort($queue);
		$executed = 0;

		foreach ($queue as $item) {
			$this->executeTask($item);
			$executed++;
		}

		$this->log(sprintf('[%s] Выполнено задач: %d', $time->format('Y-m-d H:i:s'), $executed));

		return 0;
	}

	private function buildRunnableItem(array $parent, array $task, bool $isSubtask): array
	{
		return [
			'id' => (int) ($parent['id'] ?? 0),
			'subtask_id' => $isSubtask ? (int) ($task['id'] ?? 0) : 0,
			'name' => trim((string) ($task['name'] ?? '')),
			'class' => trim((string) ($task['class'] ?? '')),
			'method' => trim((string) ($task['method'] ?? '')),
			'params' => trim((string) ($task['params'] ?? '')),
			'important' => !empty($task['important']),
			'urgent' => !empty($task['urgent']),
		];
	}

	private function executeTask(array $task): void
	{
		$class = trim((string) ($task['class'] ?? ''));
		$method = trim((string) ($task['method'] ?? ''));
		$name = trim((string) ($task['name'] ?? ''));
		$id = (int) ($task['id'] ?? 0);
		$subtaskId = (int) ($task['subtask_id'] ?? 0);
		$label = $subtaskId > 0 ? sprintf('task:%d.%d', $id, $subtaskId) : sprintf('task:%d', $id);

		if ($class === '' || $method === '') {
			$this->log(sprintf('[%s] Пропуск "%s": не указан класс или метод.', $label, $name));

			return;
		}

		if (!class_exists($class)) {
			$this->log(sprintf('[%s] Класс не найден: %s', $label, $class));

			return;
		}

		try {
			$instance = new $class();
		} catch (Throwable $exception) {
			$this->log(sprintf('[%s] Не удалось создать класс %s: %s', $label, $class, $exception->getMessage()));

			return;
		}

		if (!method_exists($instance, $method)) {
			$this->log(sprintf('[%s] Метод не найден: %s::%s', $label, $class, $method));

			return;
		}

		$reflection = new \ReflectionMethod($instance, $method);
		if (!$reflection->isPublic()) {
			$this->log(sprintf('[%s] Метод не public: %s::%s', $label, $class, $method));

			return;
		}

		$params = $this->parseParams((string) ($task['params'] ?? ''));

		try {
			$reflection->invokeArgs($instance, $params);
			$this->log(sprintf('[%s] Выполнена задача "%s" (%s::%s)', $label, $name, $class, $method));
		} catch (Throwable $exception) {
			$this->log(sprintf(
				'[%s] Ошибка "%s" (%s::%s): %s',
				$label,
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
