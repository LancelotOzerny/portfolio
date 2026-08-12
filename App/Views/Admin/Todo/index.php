<?php
/** @var array $data */

$columns = is_array($data['columns'] ?? null) ? $data['columns'] : [];
$tasksByColumn = is_array($data['tasksByColumn'] ?? null) ? $data['tasksByColumn'] : [];
$error = trim((string) ($data['error'] ?? ''));
$csrfToken = (string) ($data['csrfToken'] ?? '');

$boardPayload = [
	'columns' => [],
	'tasks' => [],
];

foreach ($columns as $column) {
	$columnId = (int) ($column->id ?? 0);
	$boardPayload['columns'][] = [
		'id' => $columnId,
		'code' => (string) ($column->code ?? ''),
		'title' => (string) ($column->title ?? ''),
		'color' => (string) ($column->color ?? '#6c757d'),
	];

	foreach ($tasksByColumn[$columnId] ?? [] as $task) {
		$dependencyIds = [];
		if (isset($task->dependency_ids)) {
			$decoded = json_decode((string) $task->dependency_ids, true);
			if (is_array($decoded)) {
				foreach ($decoded as $dependencyId) {
					$dependencyId = (int) $dependencyId;
					if ($dependencyId > 0) {
						$dependencyIds[] = $dependencyId;
					}
				}
			}
		} elseif (!empty($task->dependency_id)) {
			$dependencyId = (int) $task->dependency_id;
			if ($dependencyId > 0) {
				$dependencyIds[] = $dependencyId;
			}
		}

		$subtasks = [];
		if (!empty($task->subtasks)) {
			$decodedSubtasks = json_decode((string) $task->subtasks, true);
			if (is_array($decodedSubtasks)) {
				foreach ($decodedSubtasks as $subtask) {
					if (!is_array($subtask)) {
						continue;
					}

					$title = trim((string) ($subtask['title'] ?? ''));
					if ($title === '') {
						continue;
					}

					$subtasks[] = [
						'id' => (int) ($subtask['id'] ?? 0),
						'title' => $title,
						'important' => !empty($subtask['important']),
						'urgent' => !empty($subtask['urgent']),
						'done' => !empty($subtask['done']),
					];
				}
			}
		}

		$boardPayload['tasks'][] = [
			'id' => (int) ($task->id ?? 0),
			'column_id' => $columnId,
			'title' => (string) ($task->title ?? ''),
			'description' => (string) ($task->description ?? ''),
			'dependency_ids' => array_values(array_unique($dependencyIds)),
			'important' => !empty($task->is_important),
			'urgent' => !empty($task->is_urgent),
			'subtasks' => $subtasks,
		];
	}
}
?>

<section class="admin-todo" id="adminTodoBoard" data-csrf="<?= htmlspecialchars($csrfToken) ?>">
	<style>
		.admin-todo-board {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 1rem;
			align-items: start;
		}

		.admin-todo-column {
			background: #fff;
			border: 1px solid #e9ecef;
			border-radius: 0.75rem;
			box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.04);
			min-height: 420px;
			display: flex;
			flex-direction: column;
		}

		.admin-todo-column__header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 0.75rem;
			padding: 0.9rem 1rem;
			border-bottom: 1px solid #eef1f4;
		}

		.admin-todo-column__title-wrap {
			display: flex;
			align-items: center;
			gap: 0.55rem;
			min-width: 0;
		}

		.admin-todo-column__swatch {
			width: 0.85rem;
			height: 0.85rem;
			border-radius: 999px;
			flex-shrink: 0;
			border: 1px solid rgba(0, 0, 0, 0.08);
		}

		.admin-todo-column__title {
			margin: 0;
			font-size: 1rem;
			font-weight: 600;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.admin-todo-column__actions {
			display: inline-flex;
			align-items: center;
			gap: 0.4rem;
		}

		.admin-todo-column__color {
			width: 2rem;
			height: 2rem;
			padding: 0;
			border: 1px solid #dee2e6;
			border-radius: 0.4rem;
			background: #fff;
			cursor: pointer;
		}

		.admin-todo-column__list {
			flex: 1;
			display: flex;
			flex-direction: column;
			gap: 0.65rem;
			padding: 0.85rem;
			min-height: 280px;
		}

		.admin-todo-column__list.is-dragover {
			background: rgba(13, 110, 253, 0.04);
			outline: 2px dashed rgba(13, 110, 253, 0.25);
			outline-offset: -6px;
			border-radius: 0.5rem;
		}

		.admin-todo-task {
			border: 1px solid rgba(0, 0, 0, 0.06);
			border-left: 3px solid var(--todo-color, #6c757d);
			border-radius: 0.55rem;
			padding: 0.75rem 0.85rem;
			background: color-mix(in srgb, var(--todo-color, #6c757d) 16%, white);
			cursor: grab;
			user-select: none;
			transition: transform 0.12s ease, box-shadow 0.12s ease;
		}

		.admin-todo-task:hover {
			transform: translateY(-1px);
			box-shadow: 0 0.35rem 0.85rem rgba(0, 0, 0, 0.08);
		}

		.admin-todo-task.is-dragging {
			opacity: 0.55;
			cursor: grabbing;
		}

		.admin-todo-task--locked {
			background: #f1f3f5;
			border-color: #dee2e6;
			border-left-color: #adb5bd;
			color: #6c757d;
			cursor: pointer;
			box-shadow: none;
		}

		.admin-todo-task--locked:hover {
			transform: none;
			box-shadow: none;
		}

		.admin-todo-task--locked[draggable="false"] {
			cursor: pointer;
		}

		.admin-todo-task__head {
			display: flex;
			align-items: flex-start;
			gap: 0.45rem;
		}

		.admin-todo-task__lock {
			flex: 0 0 auto;
			width: 0.95rem;
			height: 0.95rem;
			margin-top: 0.12rem;
			color: #6c757d;
		}

		.admin-todo-task__lock svg {
			display: block;
			width: 100%;
			height: 100%;
		}

		.admin-todo-task__title {
			margin: 0;
			font-size: 0.95rem;
			font-weight: 600;
			color: #212529;
			word-break: break-word;
		}

		.admin-todo-task--locked .admin-todo-task__title {
			color: #6c757d;
			font-weight: 500;
		}

		.admin-todo-task__hint {
			margin: 0.35rem 0 0;
			color: #868e96;
			font-size: 0.78rem;
			line-height: 1.35;
		}

		.admin-todo-task__meta {
			display: flex;
			flex-wrap: wrap;
			gap: 0.3rem;
			margin-top: 0.45rem;
		}

		.admin-todo-task__badge {
			display: inline-block;
			padding: 0.1rem 0.4rem;
			border-radius: 0.3rem;
			font-size: 0.7rem;
			font-weight: 600;
			line-height: 1.3;
			border: 1px solid transparent;
		}

		.admin-todo-task__badge--important {
			background: #e7f1ff;
			color: #0d6efd;
			border-color: #b6d4fe;
		}

		.admin-todo-task__badge--urgent {
			background: #fff3cd;
			color: #997404;
			border-color: #ffe69c;
		}

		.admin-todo-task__subtasks {
			margin: 0.45rem 0 0;
			padding: 0;
			list-style: none;
			display: flex;
			flex-direction: column;
			gap: 0.25rem;
		}

		.admin-todo-task__subtask {
			display: flex;
			align-items: flex-start;
			gap: 0.35rem;
			font-size: 0.78rem;
			color: #495057;
			line-height: 1.3;
		}

		.admin-todo-task__subtask--done {
			color: #868e96;
			text-decoration: line-through;
		}

		.admin-todo-task__subtask-marks {
			display: inline-flex;
			gap: 0.2rem;
			flex-shrink: 0;
		}

		.admin-todo-task__subtask-mark {
			width: 0.55rem;
			height: 0.55rem;
			border-radius: 999px;
			margin-top: 0.28rem;
			background: #ced4da;
		}

		.admin-todo-task__subtask-mark--important {
			background: #0d6efd;
		}

		.admin-todo-task__subtask-mark--urgent {
			background: #ffc107;
		}

		.admin-todo-subtask-row {
			border: 1px solid #e9ecef;
			border-radius: 0.5rem;
			padding: 0.75rem;
			margin-bottom: 0.65rem;
			background: #f8f9fa;
		}

		.admin-todo-empty {
			margin: 0;
			padding: 1rem;
			color: #6c757d;
			font-size: 0.9rem;
			text-align: center;
			border: 1px dashed #dee2e6;
			border-radius: 0.5rem;
		}

		@media (max-width: 991.98px) {
			.admin-todo-board {
				grid-template-columns: 1fr;
			}
		}
	</style>

	<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
		<div>
			<h1 class="h4 mb-1">To Do List</h1>
			<p class="text-secondary mb-0">Задачи разработки по колонкам.</p>
		</div>
		<div class="d-flex flex-wrap gap-2">
			<?php if ($error === '' && $columns !== []): ?>
				<button type="button" class="btn btn-outline-danger btn-sm" id="todoClearDone">Очистить</button>
			<?php endif; ?>
			<a href="/admin/" class="btn btn-outline-secondary btn-sm">Назад в админку</a>
		</div>
	</div>

	<?php if ($error !== ''): ?>
		<div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
	<?php elseif ($columns === []): ?>
		<div class="alert alert-warning mb-0">Колонки To Do List не найдены. Выполните миграцию <code>admin_todo_board.sql</code>.</div>
	<?php else: ?>
		<div class="admin-todo-board" id="todoBoard"></div>
	<?php endif; ?>

	<div class="modal fade" id="todoTaskModal" tabindex="-1" aria-labelledby="todoTaskModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<form id="todoTaskForm">
					<div class="modal-header">
						<h2 class="modal-title h5" id="todoTaskModalTitle">Задача</h2>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
					</div>
					<div class="modal-body">
						<input type="hidden" id="todoTaskId" value="">
						<input type="hidden" id="todoTaskColumnId" value="">

						<ul class="nav nav-tabs mb-3" id="todoTaskTabs" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="todo-tab-main-link" data-bs-toggle="tab" data-bs-target="#todo-tab-main" type="button" role="tab" aria-controls="todo-tab-main" aria-selected="true">Основное</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="todo-tab-subtasks-link" data-bs-toggle="tab" data-bs-target="#todo-tab-subtasks" type="button" role="tab" aria-controls="todo-tab-subtasks" aria-selected="false">Подзадачи</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="todo-tab-deps-link" data-bs-toggle="tab" data-bs-target="#todo-tab-deps" type="button" role="tab" aria-controls="todo-tab-deps" aria-selected="false">Зависимости</button>
							</li>
						</ul>

						<div class="tab-content" id="todoTaskTabsContent">
							<div class="tab-pane fade show active" id="todo-tab-main" role="tabpanel" aria-labelledby="todo-tab-main-link" tabindex="0">
								<div class="mb-3">
									<label class="form-label" for="todoTaskTitle">Название</label>
									<input type="text" class="form-control" id="todoTaskTitle" maxlength="255" required>
								</div>
								<div class="mb-3">
									<label class="form-label" for="todoTaskDescription">Описание</label>
									<textarea class="form-control" id="todoTaskDescription" rows="8"></textarea>
								</div>
								<div class="d-flex flex-wrap gap-3">
									<div class="form-check">
										<input class="form-check-input" type="checkbox" id="todoTaskImportant" value="1">
										<label class="form-check-label" for="todoTaskImportant">Важно</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" id="todoTaskUrgent" value="1">
										<label class="form-check-label" for="todoTaskUrgent">Срочно</label>
									</div>
								</div>
							</div>

							<div class="tab-pane fade" id="todo-tab-subtasks" role="tabpanel" aria-labelledby="todo-tab-subtasks-link" tabindex="0">
								<div class="d-flex justify-content-between align-items-center gap-2 mb-3">
									<label class="form-label mb-0">Подзадачи</label>
									<button type="button" class="btn btn-outline-secondary btn-sm" id="todoSubtaskAdd">Добавить</button>
								</div>
								<div id="todoSubtasksList"></div>
							</div>

							<div class="tab-pane fade" id="todo-tab-deps" role="tabpanel" aria-labelledby="todo-tab-deps-link" tabindex="0">
								<label class="form-label" for="todoTaskDependency">Зависит от задач</label>
								<select class="form-select" id="todoTaskDependency" multiple size="12">
								</select>
							</div>
						</div>

						<div class="alert alert-danger d-none mt-3 mb-0" id="todoTaskError"></div>
					</div>
					<div class="modal-footer justify-content-between">
						<button type="button" class="btn btn-outline-danger d-none" id="todoTaskDelete">Удалить</button>
						<div class="d-flex gap-2 ms-auto">
							<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
							<button type="submit" class="btn btn-primary" id="todoTaskSave">Сохранить</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<script>
(() => {
	const root = document.getElementById('adminTodoBoard');
	const boardEl = document.getElementById('todoBoard');
	if (!root || !boardEl) {
		return;
	}

	const csrfToken = root.dataset.csrf || '';
	const initial = <?= json_encode($boardPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
	const state = {
		columns: Array.isArray(initial.columns) ? initial.columns : [],
		tasks: (Array.isArray(initial.tasks) ? initial.tasks : []).map((task) => ({
			...task,
			important: !!task.important,
			urgent: !!task.urgent,
			subtasks: Array.isArray(task.subtasks) ? task.subtasks : [],
			dependency_ids: Array.isArray(task.dependency_ids) ? task.dependency_ids : [],
		})),
	};

	const modalEl = document.getElementById('todoTaskModal');
	const formEl = document.getElementById('todoTaskForm');
	const titleInput = document.getElementById('todoTaskTitle');
	const descriptionInput = document.getElementById('todoTaskDescription');
	const importantInput = document.getElementById('todoTaskImportant');
	const urgentInput = document.getElementById('todoTaskUrgent');
	const subtasksListEl = document.getElementById('todoSubtasksList');
	const subtaskAddBtn = document.getElementById('todoSubtaskAdd');
	const dependencySelect = document.getElementById('todoTaskDependency');
	const taskIdInput = document.getElementById('todoTaskId');
	const columnIdInput = document.getElementById('todoTaskColumnId');
	const modalTitle = document.getElementById('todoTaskModalTitle');
	const deleteBtn = document.getElementById('todoTaskDelete');
	const errorEl = document.getElementById('todoTaskError');

	let modalInstance = null;
	let dragTaskId = 0;
	let suppressClick = false;
	let modalBackdrop = null;

	const lockIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path></svg>';

	const getModal = () => {
		if (!modalEl || !window.bootstrap || !window.bootstrap.Modal) {
			return null;
		}

		if (!modalInstance) {
			modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
		}

		return modalInstance;
	};

	const showModal = () => {
		const instance = getModal();
		if (instance) {
			instance.show();
			return;
		}

		modalEl.classList.add('show');
		modalEl.style.display = 'block';
		modalEl.removeAttribute('aria-hidden');
		modalEl.setAttribute('aria-modal', 'true');
		document.body.classList.add('modal-open');

		if (!modalBackdrop) {
			modalBackdrop = document.createElement('div');
			modalBackdrop.className = 'modal-backdrop fade show';
			document.body.appendChild(modalBackdrop);
		}
	};

	const activateMainTab = () => {
		const mainTabLink = document.getElementById('todo-tab-main-link');
		if (!mainTabLink) {
			return;
		}

		if (window.bootstrap && window.bootstrap.Tab) {
			window.bootstrap.Tab.getOrCreateInstance(mainTabLink).show();
			return;
		}

		modalEl.querySelectorAll('#todoTaskTabs .nav-link').forEach((link) => {
			const isActive = link === mainTabLink;
			link.classList.toggle('active', isActive);
			link.setAttribute('aria-selected', isActive ? 'true' : 'false');
		});

		modalEl.querySelectorAll('#todoTaskTabsContent .tab-pane').forEach((pane) => {
			const isActive = pane.id === 'todo-tab-main';
			pane.classList.toggle('show', isActive);
			pane.classList.toggle('active', isActive);
		});
	};

	const hideModal = () => {
		const instance = getModal();
		if (instance) {
			instance.hide();
			return;
		}

		modalEl.classList.remove('show');
		modalEl.style.display = 'none';
		modalEl.setAttribute('aria-hidden', 'true');
		modalEl.removeAttribute('aria-modal');
		document.body.classList.remove('modal-open');

		if (modalBackdrop) {
			modalBackdrop.remove();
			modalBackdrop = null;
		}
	};

	const escapeHtml = (value) => String(value)
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');

	const getColumn = (columnId) => state.columns.find((column) => Number(column.id) === Number(columnId)) || null;

	const getTask = (taskId) => state.tasks.find((task) => Number(task.id) === Number(taskId)) || null;

	const priorityRank = (item) => {
		const important = !!(item && item.important);
		const urgent = !!(item && item.urgent);
		if (important && urgent) {
			return 0;
		}
		if (urgent) {
			return 1;
		}
		if (important) {
			return 2;
		}
		return 3;
	};

	const sortByPriority = (items) => [...items].sort((a, b) => {
		const rankCompare = priorityRank(a) - priorityRank(b);
		if (rankCompare !== 0) {
			return rankCompare;
		}
		return Number(a.id || 0) - Number(b.id || 0);
	});

	const normalizeSubtasks = (value) => {
		if (!Array.isArray(value)) {
			return [];
		}

		const result = [];
		value.forEach((item, index) => {
			if (!item || typeof item !== 'object') {
				return;
			}

			const title = String(item.title || '').trim();
			if (!title) {
				return;
			}

			result.push({
				id: Number(item.id || index + 1),
				title,
				important: !!item.important,
				urgent: !!item.urgent,
				done: !!item.done,
			});
		});

		return sortByPriority(result);
	};

	const getTasksForColumn = (columnId) => {
		const tasks = state.tasks.filter((task) => Number(task.column_id) === Number(columnId));
		return [...tasks].sort((a, b) => {
			const rankCompare = priorityRank(a) - priorityRank(b);
			if (rankCompare !== 0) {
				return rankCompare;
			}
			return state.tasks.indexOf(a) - state.tasks.indexOf(b);
		});
	};

	const getDoneColumnId = () => {
		const doneColumn = state.columns.find((column) => String(column.code) === 'done');
		return doneColumn ? Number(doneColumn.id) : 0;
	};

	const getPlannedColumnId = () => {
		const plannedColumn = state.columns.find((column) => String(column.code) === 'planned');
		return plannedColumn ? Number(plannedColumn.id) : 0;
	};

	const isTaskDone = (task) => {
		if (!task) {
			return false;
		}

		const doneColumnId = getDoneColumnId();
		return doneColumnId > 0 && Number(task.column_id) === doneColumnId;
	};

	const isTaskLocked = (task) => {
		const dependencyIds = normalizeDependencyIds(task && task.dependency_ids);
		if (dependencyIds.length === 0) {
			return false;
		}

		return dependencyIds.some((dependencyId) => {
			const parent = getTask(dependencyId);
			return parent && !isTaskDone(parent);
		});
	};

	const getBlockingParents = (task) => {
		return normalizeDependencyIds(task && task.dependency_ids)
			.map((dependencyId) => getTask(dependencyId))
			.filter((parent) => parent && !isTaskDone(parent));
	};

	const normalizeDependencyIds = (value) => {
		if (!Array.isArray(value)) {
			const singleId = Number(value || 0);
			return singleId > 0 ? [singleId] : [];
		}

		const result = [];
		value.forEach((item) => {
			const id = Number(item);
			if (id > 0 && !result.includes(id)) {
				result.push(id);
			}
		});
		return result;
	};

	const removeDependencyFromState = (parentId) => {
		const id = Number(parentId);
		if (id <= 0) {
			return;
		}

		state.tasks = state.tasks.map((task) => {
			const dependencyIds = normalizeDependencyIds(task.dependency_ids).filter((dependencyId) => dependencyId !== id);
			return {
				...task,
				dependency_ids: dependencyIds,
			};
		});
	};

	const ensureLockedTasksInPlanned = () => {
		const plannedColumnId = getPlannedColumnId();
		if (plannedColumnId <= 0) {
			return false;
		}

		let changed = false;
		state.tasks = state.tasks.map((task) => {
			if (!isTaskLocked(task) || Number(task.column_id) === plannedColumnId) {
				return task;
			}

			changed = true;
			return {
				...task,
				column_id: plannedColumnId,
			};
		});

		return changed;
	};

	const fillDependencyOptions = (currentTaskId, selectedDependencyIds) => {
		const currentId = Number(currentTaskId || 0);
		const selectedIds = new Set(normalizeDependencyIds(selectedDependencyIds));
		const options = [];

		state.tasks.forEach((task) => {
			const taskId = Number(task.id);
			if (taskId <= 0 || taskId === currentId || isTaskDone(task)) {
				return;
			}

			options.push(
				'<option value="' + taskId + '"' + (selectedIds.has(taskId) ? ' selected' : '') + '>'
				+ escapeHtml(task.title || ('Задача #' + taskId))
				+ '</option>'
			);
		});

		dependencySelect.innerHTML = options.length
			? options.join('')
			: '<option value="" disabled>Пока нет доступных задач</option>';
	};

	const getSelectedDependencyIds = () => Array.from(dependencySelect.selectedOptions)
		.map((option) => Number(option.value))
		.filter((id) => id > 0);

	const showError = (message) => {
		errorEl.textContent = message || 'Ошибка.';
		errorEl.classList.remove('d-none');
	};

	const clearError = () => {
		errorEl.textContent = '';
		errorEl.classList.add('d-none');
	};

	const postForm = async (url, payload) => {
		const body = new FormData();
		body.append('_csrf', csrfToken);
		Object.entries(payload).forEach(([key, value]) => {
			if (Array.isArray(value)) {
				if (key === 'subtasks') {
					body.append(key, JSON.stringify(value));
					return;
				}
				value.forEach((item) => body.append(key + '[]', String(item)));
				return;
			}
			if (typeof value === 'boolean') {
				body.append(key, value ? '1' : '0');
				return;
			}
			body.append(key, String(value));
		});

		const response = await fetch(url, {
			method: 'POST',
			headers: {
				'Accept': 'application/json',
			},
			body,
		});

		const data = await response.json().catch(() => null);
		if (!response.ok || !data || !data.success) {
			throw new Error((data && data.error) || 'Не удалось выполнить запрос.');
		}

		return data.data || {};
	};

	const createSubtaskRow = (subtask = {}) => {
		const row = document.createElement('div');
		row.className = 'admin-todo-subtask-row';
		row.dataset.subtaskRow = '1';
		row.innerHTML = `
			<div class="d-flex justify-content-between align-items-center gap-2 mb-2">
				<strong class="small mb-0">Подзадача</strong>
				<button type="button" class="btn btn-outline-danger btn-sm" data-remove-subtask>Удалить</button>
			</div>
			<div class="mb-2">
				<input type="text" class="form-control form-control-sm" data-subtask-title maxlength="255" placeholder="Название" value="${escapeHtml(subtask.title || '')}">
			</div>
			<div class="d-flex flex-wrap gap-3">
				<div class="form-check">
					<input class="form-check-input" type="checkbox" data-subtask-important ${subtask.important ? 'checked' : ''}>
					<label class="form-check-label">Важно</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" data-subtask-urgent ${subtask.urgent ? 'checked' : ''}>
					<label class="form-check-label">Срочно</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="checkbox" data-subtask-done ${subtask.done ? 'checked' : ''}>
					<label class="form-check-label">Готово</label>
				</div>
			</div>
		`;

		row.querySelector('[data-remove-subtask]').addEventListener('click', () => {
			row.remove();
		});

		return row;
	};

	const renderSubtasksForm = (subtasks) => {
		subtasksListEl.innerHTML = '';
		normalizeSubtasks(subtasks).forEach((subtask) => {
			subtasksListEl.appendChild(createSubtaskRow(subtask));
		});
	};

	const collectSubtasksFromForm = () => {
		const result = [];
		subtasksListEl.querySelectorAll('[data-subtask-row]').forEach((row, index) => {
			const title = String(row.querySelector('[data-subtask-title]')?.value || '').trim();
			if (!title) {
				return;
			}

			result.push({
				id: index + 1,
				title,
				important: !!row.querySelector('[data-subtask-important]')?.checked,
				urgent: !!row.querySelector('[data-subtask-urgent]')?.checked,
				done: !!row.querySelector('[data-subtask-done]')?.checked,
			});
		});

		return normalizeSubtasks(result);
	};

	const openCreateModal = (columnId) => {
		clearError();
		activateMainTab();
		taskIdInput.value = '';
		columnIdInput.value = String(columnId);
		titleInput.value = '';
		descriptionInput.value = '';
		importantInput.checked = false;
		urgentInput.checked = false;
		renderSubtasksForm([]);
		fillDependencyOptions(0, []);
		modalTitle.textContent = 'Новая задача';
		deleteBtn.classList.add('d-none');
		showModal();
		window.setTimeout(() => titleInput.focus(), 150);
	};

	const openEditModal = (taskId) => {
		const task = getTask(taskId);
		if (!task) {
			return;
		}

		clearError();
		activateMainTab();
		taskIdInput.value = String(task.id);
		columnIdInput.value = String(task.column_id);
		titleInput.value = task.title || '';
		descriptionInput.value = task.description || '';
		importantInput.checked = !!task.important;
		urgentInput.checked = !!task.urgent;
		renderSubtasksForm(task.subtasks || []);
		fillDependencyOptions(task.id, task.dependency_ids || []);
		modalTitle.textContent = isTaskLocked(task) ? 'Задача (ожидает зависимости)' : 'Задача';
		deleteBtn.classList.remove('d-none');
		showModal();
		window.setTimeout(() => titleInput.focus(), 150);
	};

	const collectOrderedIds = (listEl) => Array.from(listEl.querySelectorAll('[data-task-id]'))
		.map((node) => Number(node.dataset.taskId))
		.filter((id) => id > 0);

	const syncBoardFromDom = () => {
		const byId = new Map(state.tasks.map((task) => [Number(task.id), task]));
		const nextTasks = [];

		state.columns.forEach((column) => {
			const listEl = boardEl.querySelector('[data-column-list="' + column.id + '"]');
			if (!listEl) {
				return;
			}

			collectOrderedIds(listEl).forEach((taskId) => {
				const task = byId.get(taskId);
				if (!task) {
					return;
				}

				nextTasks.push({
					...task,
					column_id: Number(column.id),
				});
			});
		});

		state.tasks = nextTasks;
	};

	const render = () => {
		boardEl.innerHTML = state.columns.map((column) => {
			const tasks = getTasksForColumn(column.id);
			const tasksHtml = tasks.length
				? tasks.map((task) => {
					const locked = isTaskLocked(task);
					const blockingParents = locked ? getBlockingParents(task) : [];
					const lockHtml = locked
						? '<span class="admin-todo-task__lock" title="Ожидает выполнения других задач">' + lockIconSvg + '</span>'
						: '';
					const hintHtml = blockingParents.length
						? '<p class="admin-todo-task__hint">Ждёт: ' + escapeHtml(blockingParents.map((parent) => parent.title || ('#' + parent.id)).join(', ')) + '</p>'
						: '';

					const badges = [];
					if (task.important) {
						badges.push('<span class="admin-todo-task__badge admin-todo-task__badge--important">Важно</span>');
					}
					if (task.urgent) {
						badges.push('<span class="admin-todo-task__badge admin-todo-task__badge--urgent">Срочно</span>');
					}
					const badgesHtml = badges.length
						? '<div class="admin-todo-task__meta">' + badges.join('') + '</div>'
						: '';

					const subtasks = normalizeSubtasks(task.subtasks);
					const subtasksHtml = subtasks.length
						? '<ul class="admin-todo-task__subtasks">' + subtasks.map((subtask) => {
							const marks = `
								<span class="admin-todo-task__subtask-marks">
									<span class="admin-todo-task__subtask-mark${subtask.important ? ' admin-todo-task__subtask-mark--important' : ''}" title="Важно"></span>
									<span class="admin-todo-task__subtask-mark${subtask.urgent ? ' admin-todo-task__subtask-mark--urgent' : ''}" title="Срочно"></span>
								</span>
							`;
							return '<li class="admin-todo-task__subtask' + (subtask.done ? ' admin-todo-task__subtask--done' : '') + '">'
								+ marks
								+ '<span>' + escapeHtml(subtask.title) + '</span>'
								+ '</li>';
						}).join('') + '</ul>'
						: '';

					return `
					<article
						class="admin-todo-task${locked ? ' admin-todo-task--locked' : ''}"
						draggable="${locked ? 'false' : 'true'}"
						data-task-id="${Number(task.id)}"
						style="--todo-color: ${escapeHtml(column.color)}"
					>
						<div class="admin-todo-task__head">
							${lockHtml}
							<p class="admin-todo-task__title">${escapeHtml(task.title)}</p>
						</div>
						${badgesHtml}
						${subtasksHtml}
						${hintHtml}
					</article>
				`;
				}).join('')
				: '<p class="admin-todo-empty">Пока нет задач</p>';

			return `
				<section class="admin-todo-column" data-column-id="${Number(column.id)}" style="--todo-color: ${escapeHtml(column.color)}">
					<div class="admin-todo-column__header">
						<div class="admin-todo-column__title-wrap">
							<span class="admin-todo-column__swatch" style="background: ${escapeHtml(column.color)}"></span>
							<h2 class="admin-todo-column__title">${escapeHtml(column.title)}</h2>
						</div>
						<div class="admin-todo-column__actions">
							<input
								type="color"
								class="admin-todo-column__color"
								value="${escapeHtml(column.color)}"
								data-color-column="${Number(column.id)}"
								title="Цвет колонки"
								aria-label="Цвет колонки ${escapeHtml(column.title)}"
							>
							<button
								type="button"
								class="btn btn-outline-primary btn-sm"
								data-add-column="${Number(column.id)}"
								title="Добавить задачу"
								aria-label="Добавить задачу"
							>+</button>
						</div>
					</div>
					<div class="admin-todo-column__list" data-column-list="${Number(column.id)}">
						${tasksHtml}
					</div>
				</section>
			`;
		}).join('');

		bindBoardEvents();
	};

	const persistBoardOrder = async () => {
		syncBoardFromDom();
		ensureLockedTasksInPlanned();
		render();

		for (const column of state.columns) {
			const orderedIds = getTasksForColumn(column.id).map((task) => Number(task.id)).filter((id) => id > 0);
			await postForm('/admin/development/todo/tasks/reorder/', {
				column_id: Number(column.id),
				ordered_ids: orderedIds,
			});
		}
	};

	const bindBoardEvents = () => {
		boardEl.querySelectorAll('[data-add-column]').forEach((button) => {
			button.addEventListener('click', () => {
				openCreateModal(Number(button.dataset.addColumn));
			});
		});

		boardEl.querySelectorAll('[data-color-column]').forEach((input) => {
			input.addEventListener('change', async () => {
				const columnId = Number(input.dataset.colorColumn);
				const color = String(input.value || '').toLowerCase();
				const column = getColumn(columnId);
				if (!column) {
					return;
				}

				try {
					const result = await postForm('/admin/development/todo/columns/' + columnId + '/color/', { color });
					column.color = (result.column && result.column.color) || color;
					render();
				} catch (error) {
					window.alert(error.message || 'Не удалось сохранить цвет.');
					input.value = column.color;
				}
			});
		});

		boardEl.querySelectorAll('[data-task-id]').forEach((taskEl) => {
			taskEl.addEventListener('click', () => {
				if (suppressClick) {
					suppressClick = false;
					return;
				}
				openEditModal(Number(taskEl.dataset.taskId));
			});

			taskEl.addEventListener('dragstart', (event) => {
				const task = getTask(Number(taskEl.dataset.taskId));
				if (!task || isTaskLocked(task) || taskEl.getAttribute('draggable') === 'false') {
					event.preventDefault();
					return;
				}

				dragTaskId = Number(taskEl.dataset.taskId);
				suppressClick = true;
				taskEl.classList.add('is-dragging');
				if (event.dataTransfer) {
					event.dataTransfer.effectAllowed = 'move';
					event.dataTransfer.setData('text/plain', String(dragTaskId));
				}
			});

			taskEl.addEventListener('dragend', () => {
				taskEl.classList.remove('is-dragging');
				dragTaskId = 0;
				boardEl.querySelectorAll('.admin-todo-column__list').forEach((list) => {
					list.classList.remove('is-dragover');
				});
				window.setTimeout(() => {
					suppressClick = false;
				}, 0);
			});
		});

		boardEl.querySelectorAll('[data-column-list]').forEach((listEl) => {
			listEl.addEventListener('dragover', (event) => {
				event.preventDefault();
				listEl.classList.add('is-dragover');

				const dragging = boardEl.querySelector('.admin-todo-task.is-dragging');
				if (!dragging) {
					return;
				}

				const empty = listEl.querySelector('.admin-todo-empty');
				if (empty) {
					empty.remove();
				}

				const afterElement = getDragAfterElement(listEl, event.clientY);
				if (afterElement == null) {
					listEl.appendChild(dragging);
				} else {
					listEl.insertBefore(dragging, afterElement);
				}
			});

			listEl.addEventListener('dragleave', () => {
				listEl.classList.remove('is-dragover');
			});

			listEl.addEventListener('drop', async (event) => {
				event.preventDefault();
				listEl.classList.remove('is-dragover');

				try {
					await persistBoardOrder();
					render();
				} catch (error) {
					window.alert(error.message || 'Не удалось сохранить порядок.');
					window.location.reload();
				}
			});
		});
	};

	const getDragAfterElement = (container, y) => {
		const elements = [...container.querySelectorAll('.admin-todo-task:not(.is-dragging)')];
		return elements.reduce((closest, child) => {
			const box = child.getBoundingClientRect();
			const offset = y - box.top - box.height / 2;
			if (offset < 0 && offset > closest.offset) {
				return { offset, element: child };
			}
			return closest;
		}, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
	};

	formEl.addEventListener('submit', async (event) => {
		event.preventDefault();
		clearError();

		const taskId = Number(taskIdInput.value || 0);
		const plannedColumnId = getPlannedColumnId();
		let columnId = Number(columnIdInput.value || 0);
		const title = titleInput.value.trim();
		const description = descriptionInput.value.trim();
		const important = !!importantInput.checked;
		const urgent = !!urgentInput.checked;
		const subtasks = collectSubtasksFromForm();
		const dependencyIds = getSelectedDependencyIds();
		const willBeLocked = dependencyIds.some((dependencyId) => {
			const parent = getTask(dependencyId);
			return parent && !isTaskDone(parent);
		});

		if (!title) {
			showError('Введите название задачи.');
			return;
		}

		if (willBeLocked && plannedColumnId > 0) {
			columnId = plannedColumnId;
			columnIdInput.value = String(plannedColumnId);
		}

		try {
			if (taskId > 0) {
				const result = await postForm('/admin/development/todo/tasks/' + taskId + '/update/', {
					title,
					description,
					important,
					urgent,
					subtasks,
					dependency_ids: dependencyIds,
				});
				const index = state.tasks.findIndex((task) => Number(task.id) === taskId);
				if (index >= 0) {
					state.tasks[index] = result.task;
				}
			} else {
				const result = await postForm('/admin/development/todo/tasks/create/', {
					column_id: columnId,
					title,
					description,
					important,
					urgent,
					subtasks,
					dependency_ids: dependencyIds,
				});
				if (result.task) {
					state.tasks.push(result.task);
				}
			}

			ensureLockedTasksInPlanned();
			hideModal();
			render();
		} catch (error) {
			showError(error.message || 'Не удалось сохранить задачу.');
		}
	});

	subtaskAddBtn.addEventListener('click', () => {
		subtasksListEl.appendChild(createSubtaskRow());
	});

	const clearDoneBtn = document.getElementById('todoClearDone');
	clearDoneBtn?.addEventListener('click', async () => {
		const doneColumnId = getDoneColumnId();
		const doneTasks = doneColumnId > 0 ? getTasksForColumn(doneColumnId) : [];
		if (doneTasks.length === 0) {
			return;
		}

		if (!window.confirm('Удалить все задачи в колонке «Готово» (' + doneTasks.length + ')?')) {
			return;
		}

		try {
			await postForm('/admin/development/todo/tasks/clear-done/', {});
			doneTasks.forEach((task) => removeDependencyFromState(task.id));
			state.tasks = state.tasks.filter((task) => Number(task.column_id) !== doneColumnId);
			render();
		} catch (error) {
			window.alert(error.message || 'Не удалось очистить задачи.');
		}
	});

	deleteBtn.addEventListener('click', async () => {
		const taskId = Number(taskIdInput.value || 0);
		if (taskId <= 0) {
			return;
		}

		if (!window.confirm('Удалить задачу?')) {
			return;
		}

		clearError();

		try {
			await postForm('/admin/development/todo/tasks/' + taskId + '/delete/', {});
			removeDependencyFromState(taskId);
			state.tasks = state.tasks.filter((task) => Number(task.id) !== taskId);
			hideModal();
			render();
		} catch (error) {
			showError(error.message || 'Не удалось удалить задачу.');
		}
	});

	ensureLockedTasksInPlanned();
	render();
})();
</script>
