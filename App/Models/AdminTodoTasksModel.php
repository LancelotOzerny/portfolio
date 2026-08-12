<?php

namespace Models;

use App\Services\Todo\TodoTaskPriority;
use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;
use PDO;
use Throwable;

class AdminTodoTasksModel extends BaseModel
{
	protected string $table = 'admin_todo_tasks';

	private bool $schemaReady = false;

	public function findAllOrdered(): array
	{
		$this->ensureSchema();

		$qb = (new QueryBuilder($this->table))
			->select()
			->orderBy('sort_order', 'ASC')
			->orderBy('id', 'ASC');

		return $this->sortTasks($this->execQuery($qb) ?? []);
	}

	public function findByColumnId(int $columnId): array
	{
		$this->ensureSchema();

		$qb = (new QueryBuilder($this->table))
			->select()
			->where('column_id', '=', $columnId)
			->orderBy('sort_order', 'ASC')
			->orderBy('id', 'ASC');

		return $this->sortTasks($this->execQuery($qb) ?? []);
	}

	public function findById(int $id): ?object
	{
		$this->ensureSchema();

		return parent::findById($id);
	}

	/**
	 * @param list<int> $dependencyIds
	 * @param list<array<string, mixed>> $subtasks
	 */
	public function createTask(
		int $columnId,
		string $title,
		string $description,
		array $dependencyIds = [],
		bool $important = false,
		bool $urgent = false,
		array $subtasks = []
	): int {
		$this->ensureSchema();
		$sortOrder = $this->nextSortOrder($columnId);

		$qb = (new QueryBuilder($this->table))->insert([
			'column_id' => $columnId,
			'title' => $title,
			'description' => $description,
			'dependency_ids' => $this->encodeDependencyIds($dependencyIds),
			'is_important' => $important ? 1 : 0,
			'is_urgent' => $urgent ? 1 : 0,
			'subtasks' => $this->encodeSubtasks($subtasks),
			'sort_order' => $sortOrder,
		]);

		return $this->execInsertQuery($qb);
	}

	/**
	 * @param list<int> $dependencyIds
	 * @param list<array<string, mixed>> $subtasks
	 */
	public function updateTask(
		int $id,
		string $title,
		string $description,
		array $dependencyIds = [],
		?int $columnId = null,
		bool $important = false,
		bool $urgent = false,
		array $subtasks = []
	): bool {
		$this->ensureSchema();

		$data = [
			'title' => $title,
			'description' => $description,
			'dependency_ids' => $this->encodeDependencyIds($dependencyIds),
			'is_important' => $important ? 1 : 0,
			'is_urgent' => $urgent ? 1 : 0,
			'subtasks' => $this->encodeSubtasks($subtasks),
		];

		if ($columnId !== null && $columnId > 0) {
			$data['column_id'] = $columnId;
		}

		$qb = (new QueryBuilder($this->table))
			->update($data)
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function moveToColumn(int $id, int $columnId): bool
	{
		$this->ensureSchema();

		if ($id <= 0 || $columnId <= 0) {
			return false;
		}

		$sortOrder = $this->nextSortOrder($columnId);
		$qb = (new QueryBuilder($this->table))
			->update([
				'column_id' => $columnId,
				'sort_order' => $sortOrder,
			])
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function removeDependencyReferences(int $parentId): bool
	{
		$this->ensureSchema();

		if ($parentId <= 0) {
			return true;
		}

		foreach ($this->findAllOrdered() as $task) {
			$taskId = (int) ($task->id ?? 0);
			$dependencyIds = $this->decodeDependencyIds($task->dependency_ids ?? ($task->dependency_id ?? null));
			if ($dependencyIds === [] || !in_array($parentId, $dependencyIds, true)) {
				continue;
			}

			$filtered = array_values(array_filter(
				$dependencyIds,
				static fn (int $id): bool => $id !== $parentId
			));

			$qb = (new QueryBuilder($this->table))
				->update([
					'dependency_ids' => $this->encodeDependencyIds($filtered),
				])
				->where('id', '=', $taskId);

			if (!$this->execWriteQuery($qb)) {
				return false;
			}
		}

		return true;
	}

	public function deleteById(int $id): bool
	{
		$this->ensureSchema();
		$this->removeDependencyReferences($id);

		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('id', '=', $id);

		return $this->execWriteQuery($qb);
	}

	public function reorder(int $columnId, array $orderedIds): bool
	{
		$this->ensureSchema();
		$position = 1;

		foreach ($orderedIds as $taskId) {
			$taskId = (int) $taskId;
			if ($taskId <= 0) {
				continue;
			}

			$qb = (new QueryBuilder($this->table))
				->update([
					'column_id' => $columnId,
					'sort_order' => $position,
				])
				->where('id', '=', $taskId);

			if (!$this->execWriteQuery($qb)) {
				return false;
			}

			$position++;
		}

		return true;
	}

	/**
	 * @return list<int>
	 */
	public function decodeDependencyIds(mixed $raw): array
	{
		if (is_int($raw) || (is_string($raw) && ctype_digit($raw))) {
			$id = (int) $raw;
			return $id > 0 ? [$id] : [];
		}

		if (!is_string($raw)) {
			return [];
		}

		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}

		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			return [];
		}

		$result = [];
		foreach ($decoded as $value) {
			$id = (int) $value;
			if ($id > 0) {
				$result[$id] = $id;
			}
		}

		return array_values($result);
	}

	/**
	 * @param list<int> $dependencyIds
	 */
	public function encodeDependencyIds(array $dependencyIds): ?string
	{
		$normalized = [];
		foreach ($dependencyIds as $value) {
			$id = (int) $value;
			if ($id > 0) {
				$normalized[$id] = $id;
			}
		}

		if ($normalized === []) {
			return null;
		}

		return json_encode(array_values($normalized), JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @return list<array{id: int, title: string, important: bool, urgent: bool, done: bool}>
	 */
	public function decodeSubtasks(mixed $raw): array
	{
		if (!is_string($raw)) {
			return [];
		}

		$raw = trim($raw);
		if ($raw === '') {
			return [];
		}

		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			return [];
		}

		return $this->normalizeSubtasks($decoded);
	}

	/**
	 * @param list<array<string, mixed>> $subtasks
	 */
	public function encodeSubtasks(array $subtasks): ?string
	{
		$normalized = $this->normalizeSubtasks($subtasks);
		if ($normalized === []) {
			return null;
		}

		return json_encode($normalized, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @param list<array<string, mixed>> $subtasks
	 * @return list<array{id: int, title: string, important: bool, urgent: bool, done: bool}>
	 */
	public function normalizeSubtasks(array $subtasks): array
	{
		$result = [];
		$nextId = 1;

		foreach ($subtasks as $subtask) {
			if (!is_array($subtask)) {
				continue;
			}

			$title = trim((string) ($subtask['title'] ?? ''));
			if ($title === '') {
				continue;
			}

			if (mb_strlen($title) > 255) {
				$title = mb_substr($title, 0, 255);
			}

			$id = (int) ($subtask['id'] ?? 0);
			if ($id <= 0) {
				$id = $nextId;
			}

			$nextId = max($nextId, $id + 1);

			$result[] = [
				'id' => $id,
				'title' => $title,
				'important' => !empty($subtask['important']),
				'urgent' => !empty($subtask['urgent']),
				'done' => !empty($subtask['done']),
			];
		}

		$sortable = [];
		foreach ($result as $index => $subtask) {
			$sortable[] = [
				'important' => $subtask['important'],
				'urgent' => $subtask['urgent'],
				'sort_order' => $index,
				'id' => $subtask['id'],
				'_item' => $subtask,
			];
		}

		$sorted = TodoTaskPriority::sort($sortable);

		return array_map(
			static fn (array $item): array => $item['_item'],
			$sorted
		);
	}

	public function isImportant(object $task): bool
	{
		return !empty($task->is_important);
	}

	public function isUrgent(object $task): bool
	{
		return !empty($task->is_urgent);
	}

	public function ensureSchema(): void
	{
		if ($this->schemaReady) {
			return;
		}

		$columns = $this->listColumns();
		if ($columns === []) {
			$this->schemaReady = true;
			return;
		}

		$alters = [];
		if (!isset($columns['is_important'])) {
			$alters[] = 'ADD COLUMN `is_important` TINYINT(1) NOT NULL DEFAULT 0';
		}
		if (!isset($columns['is_urgent'])) {
			$alters[] = 'ADD COLUMN `is_urgent` TINYINT(1) NOT NULL DEFAULT 0';
		}
		if (!isset($columns['subtasks'])) {
			$alters[] = 'ADD COLUMN `subtasks` TEXT NULL';
		}

		foreach ($alters as $alter) {
			$this->db->exec('ALTER TABLE `' . $this->table . '` ' . $alter);
		}

		$this->schemaReady = true;
	}

	/**
	 * @return array<string, true>
	 */
	private function listColumns(): array
	{
		try {
			$stmt = $this->db->query('SHOW COLUMNS FROM `' . $this->table . '`');
			if ($stmt === false) {
				return [];
			}

			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (Throwable) {
			return [];
		}

		$result = [];
		foreach ($rows as $row) {
			$name = strtolower((string) ($row['Field'] ?? ''));
			if ($name !== '') {
				$result[$name] = true;
			}
		}

		return $result;
	}

	/**
	 * @param list<object> $tasks
	 * @return list<object>
	 */
	private function sortTasks(array $tasks): array
	{
		$sortable = [];
		foreach ($tasks as $index => $task) {
			$sortable[] = [
				'important' => $this->isImportant($task),
				'urgent' => $this->isUrgent($task),
				'sort_order' => (int) ($task->sort_order ?? $index),
				'id' => (int) ($task->id ?? 0),
				'_item' => $task,
			];
		}

		$sorted = TodoTaskPriority::sort($sortable);

		return array_map(
			static fn (array $item): object => $item['_item'],
			$sorted
		);
	}

	private function nextSortOrder(int $columnId): int
	{
		$qb = (new QueryBuilder($this->table))
			->selectRaw('MAX(sort_order) AS max_sort')
			->where('column_id', '=', $columnId);

		$row = $this->execQuery($qb, true);
		$maxSort = (int) ($row->max_sort ?? 0);

		return $maxSort + 1;
	}
}
