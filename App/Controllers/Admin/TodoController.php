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

			$tasksModel = new AdminTodoTasksModel();
			$plannedColumnId = 0;
			foreach ($columns as $column) {
				if ((string) ($column->code ?? '') === 'planned') {
					$plannedColumnId = (int) ($column->id ?? 0);
					break;
				}
			}

			if ($plannedColumnId > 0) {
				$this->moveNewlyLockedTasksToPlanned($tasksModel, $columnsModel, $plannedColumnId);
			}

			$tasks = $tasksModel->findAllOrdered();
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
			$dependencyIds = $this->parseDependencyIds($_POST['dependency_ids'] ?? ($_POST['dependency_id'] ?? null));

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

			$model = new AdminTodoTasksModel();
			$dependencyIds = $this->resolveDependencyIds($model, $dependencyIds);

			if ($this->hasActiveDependencies($model, $dependencyIds)) {
				$columnId = $this->requirePlannedColumnId();
			}

			$taskId = $model->createTask($columnId, $title, $description, $dependencyIds);
			if ($taskId <= 0) {
				throw new \RuntimeException('Не удалось создать задачу.');
			}

			$task = $model->findById($taskId);
			if ($task === null) {
				throw new \RuntimeException('Задача создана, но не найдена.');
			}

			return [
				'task' => $this->mapTask($model, $task),
			];
		});
	}

	public function updateTask(int $id): void
	{
		$this->handleJsonRequest(function () use ($id): array {
			$title = trim((string) ($_POST['title'] ?? ''));
			$description = trim((string) ($_POST['description'] ?? ''));
			$dependencyIds = $this->parseDependencyIds($_POST['dependency_ids'] ?? ($_POST['dependency_id'] ?? null));

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

			$dependencyIds = $this->resolveDependencyIds($model, $dependencyIds, $id);
			$columnId = null;
			if ($this->hasActiveDependencies($model, $dependencyIds)) {
				$columnId = $this->requirePlannedColumnId();
			}

			if (!$model->updateTask($id, $title, $description, $dependencyIds, $columnId)) {
				throw new \RuntimeException('Не удалось сохранить задачу.');
			}

			$updated = $model->findById($id);
			if ($updated === null) {
				throw new \RuntimeException('Не удалось загрузить задачу после сохранения.');
			}

			return [
				'task' => $this->mapTask($model, $updated),
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

			$model = new AdminTodoTasksModel();
			$columnsModel = new AdminTodoColumnsModel();
			$plannedColumnId = $this->findColumnIdByCode($columnsModel, 'planned');
			$isPlannedColumn = (string) ($column->code ?? '') === 'planned';

			if (!$isPlannedColumn) {
				$tasksById = [];
				foreach ($model->findAllOrdered() as $task) {
					$tasksById[(int) ($task->id ?? 0)] = $task;
				}

				$columnsById = [];
				foreach ($columnsModel->findAllOrdered() as $boardColumn) {
					$columnsById[(int) ($boardColumn->id ?? 0)] = $boardColumn;
				}

				foreach ($normalizedIds as $taskId) {
					$task = $tasksById[$taskId] ?? null;
					if ($task === null) {
						continue;
					}

					if ($this->isTaskLocked($model, $task, $tasksById, $columnsById)) {
						throw new \InvalidArgumentException('Заблокированные задачи можно хранить только в колонке «Планируется».');
					}
				}
			}

			if (!$model->reorder($columnId, $normalizedIds)) {
				throw new \RuntimeException('Не удалось сохранить порядок задач.');
			}

			if ($plannedColumnId > 0) {
				$this->moveNewlyLockedTasksToPlanned($model, $columnsModel, $plannedColumnId);
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

	private function mapTask(AdminTodoTasksModel $model, object $task): array
	{
		$dependencyIds = $model->decodeDependencyIds($task->dependency_ids ?? ($task->dependency_id ?? null));

		return [
			'id' => (int) ($task->id ?? 0),
			'column_id' => (int) ($task->column_id ?? 0),
			'title' => (string) ($task->title ?? ''),
			'description' => (string) ($task->description ?? ''),
			'dependency_ids' => $dependencyIds,
			'sort_order' => (int) ($task->sort_order ?? 0),
		];
	}

	/**
	 * @return list<int>
	 */
	private function parseDependencyIds(mixed $value): array
	{
		if ($value === null || $value === '') {
			return [];
		}

		if (!is_array($value)) {
			$value = [$value];
		}

		$result = [];
		foreach ($value as $item) {
			$id = (int) $item;
			if ($id > 0) {
				$result[$id] = $id;
			}
		}

		return array_values($result);
	}

	/**
	 * @param list<int> $dependencyIds
	 * @return list<int>
	 */
	private function resolveDependencyIds(AdminTodoTasksModel $model, array $dependencyIds, int $taskId = 0): array
	{
		$result = [];

		foreach ($dependencyIds as $dependencyId) {
			$dependencyId = (int) $dependencyId;
			if ($dependencyId <= 0) {
				continue;
			}

			if ($taskId > 0 && $dependencyId === $taskId) {
				throw new \InvalidArgumentException('Задача не может зависеть от себя.');
			}

			$dependency = $model->findById($dependencyId);
			if ($dependency === null) {
				throw new \InvalidArgumentException('Задача-зависимость не найдена.');
			}

			$result[$dependencyId] = $dependencyId;
		}

		return array_values($result);
	}

	/**
	 * @param list<int> $dependencyIds
	 */
	private function hasActiveDependencies(AdminTodoTasksModel $model, array $dependencyIds): bool
	{
		if ($dependencyIds === []) {
			return false;
		}

		$columnsModel = new AdminTodoColumnsModel();
		$columnsById = [];
		foreach ($columnsModel->findAllOrdered() as $column) {
			$columnsById[(int) ($column->id ?? 0)] = $column;
		}

		foreach ($dependencyIds as $dependencyId) {
			$dependency = $model->findById((int) $dependencyId);
			if ($dependency === null) {
				continue;
			}

			$column = $columnsById[(int) ($dependency->column_id ?? 0)] ?? null;
			if ($column === null || (string) ($column->code ?? '') !== 'done') {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int, object> $tasksById
	 * @param array<int, object> $columnsById
	 */
	private function isTaskLocked(
		AdminTodoTasksModel $model,
		object $task,
		array $tasksById,
		array $columnsById
	): bool {
		$dependencyIds = $model->decodeDependencyIds($task->dependency_ids ?? ($task->dependency_id ?? null));
		if ($dependencyIds === []) {
			return false;
		}

		foreach ($dependencyIds as $dependencyId) {
			$parent = $tasksById[$dependencyId] ?? null;
			if ($parent === null) {
				continue;
			}

			$column = $columnsById[(int) ($parent->column_id ?? 0)] ?? null;
			if ($column === null || (string) ($column->code ?? '') !== 'done') {
				return true;
			}
		}

		return false;
	}

	private function requirePlannedColumnId(): int
	{
		$plannedColumnId = $this->findColumnIdByCode(new AdminTodoColumnsModel(), 'planned');
		if ($plannedColumnId <= 0) {
			throw new \RuntimeException('Колонка «Планируется» не найдена.');
		}

		return $plannedColumnId;
	}

	private function findColumnIdByCode(AdminTodoColumnsModel $columnsModel, string $code): int
	{
		foreach ($columnsModel->findAllOrdered() as $column) {
			if ((string) ($column->code ?? '') === $code) {
				return (int) ($column->id ?? 0);
			}
		}

		return 0;
	}

	private function moveNewlyLockedTasksToPlanned(
		AdminTodoTasksModel $model,
		AdminTodoColumnsModel $columnsModel,
		int $plannedColumnId
	): void {
		$tasksById = [];
		foreach ($model->findAllOrdered() as $task) {
			$tasksById[(int) ($task->id ?? 0)] = $task;
		}

		$columnsById = [];
		foreach ($columnsModel->findAllOrdered() as $column) {
			$columnsById[(int) ($column->id ?? 0)] = $column;
		}

		foreach ($tasksById as $taskId => $task) {
			if ($taskId <= 0) {
				continue;
			}

			if (!$this->isTaskLocked($model, $task, $tasksById, $columnsById)) {
				continue;
			}

			if ((int) ($task->column_id ?? 0) === $plannedColumnId) {
				continue;
			}

			$model->moveToColumn($taskId, $plannedColumnId);
		}
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
