<?php

namespace Controllers\Admin;

use App\Services\Security\CsrfService;
use Models\AdminTodoColumnsModel;
use Models\AdminTodoTasksModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class TodoController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$columns = [];
		$tasksByColumn = [];
		$error = '';

		try {
			$columnsModel = new AdminTodoColumnsModel();
			$columnsModel->ensureDefaults();
			$columns = $columnsModel->findAllOrdered();

			$tasks = (new AdminTodoTasksModel())->findAllOrdered();
			foreach ($tasks as $task) {
				$columnId = (int) ($task->column_id ?? 0);
				if ($columnId <= 0) {
					continue;
				}

				$tasksByColumn[$columnId][] = $task;
			}
		} catch (Throwable $exception) {
			$message = trim($exception->getMessage());
			$error = $message !== ''
				? $message
				: 'Не удалось загрузить To Do List. Выполните миграцию admin_todo_board.sql.';
		}

		Template::getInstance()->setParam('title', 'To Do List');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'columns' => $columns,
			'tasksByColumn' => $tasksByColumn,
			'error' => $error,
			'csrfToken' => (new CsrfService())->getToken(),
		]);
		Template::getInstance()->showFooter();
	}

	public function createTask(): void
	{
		$this->handleJsonRequest(function (): array {
			$columnId = (int) ($_POST['column_id'] ?? 0);
			$title = trim((string) ($_POST['title'] ?? ''));
			$description = trim((string) ($_POST['description'] ?? ''));

			if ($columnId <= 0) {
				throw new \InvalidArgumentException('Колонка не выбрана.');
			}

			if ($title === '') {
				throw new \InvalidArgumentException('Введите название задачи.');
			}

			if (mb_strlen($title) > 255) {
				throw new \InvalidArgumentException('Название задачи слишком длинное.');
			}

			$column = (new AdminTodoColumnsModel())->findById($columnId);
			if ($column === null) {
				throw new \RuntimeException('Колонка не найдена.');
			}

			$taskId = (new AdminTodoTasksModel())->createTask($columnId, $title, $description);
			if ($taskId <= 0) {
				throw new \RuntimeException('Не удалось создать задачу.');
			}

			$task = (new AdminTodoTasksModel())->findById($taskId);
			if ($task === null) {
				throw new \RuntimeException('Задача создана, но не найдена.');
			}

			return [
				'task' => $this->mapTask($task),
			];
		});
	}

	public function updateTask(int $id): void
	{
		$this->handleJsonRequest(function () use ($id): array {
			$title = trim((string) ($_POST['title'] ?? ''));
			$description = trim((string) ($_POST['description'] ?? ''));

			if ($title === '') {
				throw new \InvalidArgumentException('Введите название задачи.');
			}

			if (mb_strlen($title) > 255) {
				throw new \InvalidArgumentException('Название задачи слишком длинное.');
			}

			$model = new AdminTodoTasksModel();
			$task = $model->findById($id);
			if ($task === null) {
				throw new \RuntimeException('Задача не найдена.');
			}

			if (!$model->updateTask($id, $title, $description)) {
				throw new \RuntimeException('Не удалось сохранить задачу.');
			}

			$updated = $model->findById($id);
			if ($updated === null) {
				throw new \RuntimeException('Не удалось загрузить задачу после сохранения.');
			}

			return [
				'task' => $this->mapTask($updated),
			];
		});
	}

	public function deleteTask(int $id): void
	{
		$this->handleJsonRequest(function () use ($id): array {
			$model = new AdminTodoTasksModel();
			$task = $model->findById($id);
			if ($task === null) {
				throw new \RuntimeException('Задача не найдена.');
			}

			if (!$model->deleteById($id)) {
				throw new \RuntimeException('Не удалось удалить задачу.');
			}

			return [
				'deleted' => true,
				'id' => $id,
			];
		});
	}

	public function reorderTasks(): void
	{
		$this->handleJsonRequest(function (): array {
			$columnId = (int) ($_POST['column_id'] ?? 0);
			$orderedIds = $_POST['ordered_ids'] ?? [];

			if ($columnId <= 0) {
				throw new \InvalidArgumentException('Колонка не выбрана.');
			}

			if (!is_array($orderedIds)) {
				throw new \InvalidArgumentException('Некорректный порядок задач.');
			}

			$column = (new AdminTodoColumnsModel())->findById($columnId);
			if ($column === null) {
				throw new \RuntimeException('Колонка не найдена.');
			}

			$normalizedIds = [];
			foreach ($orderedIds as $taskId) {
				$taskId = (int) $taskId;
				if ($taskId > 0) {
					$normalizedIds[] = $taskId;
				}
			}

			if (!(new AdminTodoTasksModel())->reorder($columnId, $normalizedIds)) {
				throw new \RuntimeException('Не удалось сохранить порядок задач.');
			}

			return [
				'column_id' => $columnId,
				'ordered_ids' => $normalizedIds,
			];
		});
	}

	public function updateColumnColor(int $id): void
	{
		$this->handleJsonRequest(function () use ($id): array {
			$color = strtolower(trim((string) ($_POST['color'] ?? '')));
			if (preg_match('/^#[0-9a-f]{6}$/', $color) !== 1) {
				throw new \InvalidArgumentException('Некорректный цвет колонки.');
			}

			$model = new AdminTodoColumnsModel();
			$column = $model->findById($id);
			if ($column === null) {
				throw new \RuntimeException('Колонка не найдена.');
			}

			if (!$model->updateColor($id, $color)) {
				throw new \RuntimeException('Не удалось сохранить цвет колонки.');
			}

			return [
				'column' => [
					'id' => $id,
					'color' => $color,
				],
			];
		});
	}

	private function mapTask(object $task): array
	{
		return [
			'id' => (int) ($task->id ?? 0),
			'column_id' => (int) ($task->column_id ?? 0),
			'title' => (string) ($task->title ?? ''),
			'description' => (string) ($task->description ?? ''),
			'sort_order' => (int) ($task->sort_order ?? 0),
		];
	}

	private function handleJsonRequest(callable $callback): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!$this->ensureAdmin()) {
			http_response_code(403);
			echo json_encode(['success' => false, 'error' => 'Access denied.']);
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			http_response_code(400);
			echo json_encode(['success' => false, 'error' => 'Недействительный CSRF-токен.']);
			return;
		}

		try {
			$result = $callback();
			echo json_encode([
				'success' => true,
				'data' => $result,
			]);
		} catch (Throwable $exception) {
			http_response_code(400);
			$message = trim($exception->getMessage());
			echo json_encode([
				'success' => false,
				'error' => $message !== '' ? $message : 'Ошибка запроса.',
			]);
		}
	}

	private function ensureAdmin(): bool
	{
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			if ($this->wantsJson()) {
				return false;
			}

			header('Location: /admin/login/');
			return false;
		}

		return true;
	}

	private function wantsJson(): bool
	{
		$accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
		$contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');

		return str_contains($accept, 'application/json')
			|| str_contains($contentType, 'application/json')
			|| str_starts_with((string) ($_SERVER['REQUEST_URI'] ?? ''), '/admin/development/todo/tasks/')
			|| str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/color/');
	}
}
