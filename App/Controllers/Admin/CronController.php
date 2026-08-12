<?php

namespace Controllers\Admin;

use App\Services\Cron\CronScheduleMatcher;
use App\Services\Cron\CronTaskPriority;
use App\Services\Cron\CronTaskStorage;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class CronController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$matcher = new CronScheduleMatcher();
		$tasks = [];

		try {
			$tasks = (new CronTaskStorage())->getAll();
		} catch (Throwable $exception) {
			$error = trim($exception->getMessage());
		}

		Template::getInstance()->setParam('title', 'Cron задачи');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'tasks' => $tasks,
			'matcher' => $matcher,
			'priority' => CronTaskPriority::class,
			'cronPath' => $this->getCronPath(),
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'deleted' => isset($_GET['deleted']) && $_GET['deleted'] === '1',
			'error' => trim((string) ($_GET['error'] ?? ($error ?? ''))),
		]);
		Template::getInstance()->showFooter();
	}

	public function create(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$data = $this->validateTask($_POST);
			(new CronTaskStorage())->save($data);
			header('Location: /admin/settings/cron/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/settings/cron/', $exception);
		}
	}

	public function edit(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$task = (new CronTaskStorage())->findById($id);
		} catch (Throwable) {
			$task = null;
		}

		if ($task === null) {
			header('Location: /admin/settings/cron/?error=' . rawurlencode('Задача не найдена.'));
			return;
		}

		Template::getInstance()->setParam('title', 'Редактирование cron-задачи');
		Template::getInstance()->showHeader();
		$this->render('edit', [
			'task' => $task,
			'error' => trim((string) ($_GET['error'] ?? '')),
		]);
		Template::getInstance()->showFooter();
	}

	public function update(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$storage = new CronTaskStorage();
			if ($storage->findById($id) === null) {
				throw new \InvalidArgumentException('Задача не найдена.');
			}

			$data = $this->validateTask($_POST);
			$data['id'] = $id;
			$storage->save($data);
			header('Location: /admin/settings/cron/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/settings/cron/' . $id . '/', $exception);
		}
	}

	public function delete(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			(new CronTaskStorage())->delete($id);
			header('Location: /admin/settings/cron/?deleted=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/settings/cron/', $exception);
		}
	}

	private function validateTask(array $input): array
	{
		$name = trim((string) ($input['name'] ?? ''));
		$class = trim((string) ($input['class'] ?? ''));
		$method = trim((string) ($input['method'] ?? ''));
		$subtasks = $this->parseSubtasks($input['subtasks'] ?? []);

		if ($name === '') {
			throw new \InvalidArgumentException('Укажите название задачи.');
		}

		if ($subtasks === []) {
			$this->assertExecutableTask($class, $method, 'задачи');
		} else {
			foreach ($subtasks as $index => $subtask) {
				$this->assertExecutableTask(
					(string) ($subtask['class'] ?? ''),
					(string) ($subtask['method'] ?? ''),
					'подзадачи #' . ($index + 1)
				);
			}
		}

		$schedule = [
			'minute' => trim((string) ($input['schedule_minute'] ?? '*')) ?: '*',
			'hour' => trim((string) ($input['schedule_hour'] ?? '*')) ?: '*',
			'day' => trim((string) ($input['schedule_day'] ?? '*')) ?: '*',
			'month' => trim((string) ($input['schedule_month'] ?? '*')) ?: '*',
			'weekday' => trim((string) ($input['schedule_weekday'] ?? '*')) ?: '*',
		];

		foreach ($schedule as $field => $value) {
			if (!$this->isValidScheduleField($value)) {
				throw new \InvalidArgumentException('Некорректное значение расписания: ' . $field . '.');
			}
		}

		return [
			'id' => (int) ($input['id'] ?? 0),
			'name' => $name,
			'description' => trim((string) ($input['description'] ?? '')),
			'class' => $class,
			'method' => $method,
			'params' => trim((string) ($input['params'] ?? '')),
			'important' => !empty($input['important']),
			'urgent' => !empty($input['urgent']),
			'schedule' => $schedule,
			'enabled' => !empty($input['enabled']),
			'subtasks' => $subtasks,
		];
	}

	private function parseSubtasks(mixed $raw): array
	{
		if (!is_array($raw)) {
			return [];
		}

		$subtasks = [];
		foreach ($raw as $row) {
			if (!is_array($row)) {
				continue;
			}

			$name = trim((string) ($row['name'] ?? ''));
			if ($name === '') {
				continue;
			}

			$subtasks[] = [
				'id' => (int) ($row['id'] ?? 0),
				'name' => $name,
				'description' => trim((string) ($row['description'] ?? '')),
				'class' => trim((string) ($row['class'] ?? '')),
				'method' => trim((string) ($row['method'] ?? '')),
				'params' => trim((string) ($row['params'] ?? '')),
				'important' => !empty($row['important']),
				'urgent' => !empty($row['urgent']),
				'enabled' => !empty($row['enabled']),
			];
		}

		return $subtasks;
	}

	private function assertExecutableTask(string $class, string $method, string $context): void
	{
		if ($class === '') {
			throw new \InvalidArgumentException('Укажите класс ' . $context . '.');
		}

		if ($method === '' || !preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $method)) {
			throw new \InvalidArgumentException('Укажите корректное имя public-метода ' . $context . '.');
		}
	}

	private function isValidScheduleField(string $value): bool
	{
		if ($value === '' || $value === '*') {
			return true;
		}

		return preg_match('/^(\*|\*\/\d+|\d+(?:-\d+)?(?:\/\d+)?)(?:,(\*|\*\/\d+|\d+(?:-\d+)?(?:\/\d+)?))*$/', $value) === 1;
	}

	private function getCronPath(): string
	{
		return dirname(__DIR__, 2) . '/App/cron.php';
	}

	private function redirectWithError(string $url, Throwable $exception): void
	{
		$message = trim($exception->getMessage());
		if ($message === '') {
			$message = 'Не удалось сохранить cron-задачу.';
		}

		$separator = str_contains($url, '?') ? '&' : '?';
		header('Location: ' . $url . $separator . 'error=' . rawurlencode($message));
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
}
