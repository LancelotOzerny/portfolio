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
		$boardPayload['tasks'][] = [
			'id' => (int) ($task->id ?? 0),
			'column_id' => $columnId,
			'title' => (string) ($task->title ?? ''),
			'description' => (string) ($task->description ?? ''),
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

		.admin-todo-task__title {
			margin: 0;
			font-size: 0.95rem;
			font-weight: 600;
			color: #212529;
			word-break: break-word;
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
		<a href="/admin/" class="btn btn-outline-secondary btn-sm">Назад в админку</a>
	</div>

	<?php if ($error !== ''): ?>
		<div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
	<?php elseif ($columns === []): ?>
		<div class="alert alert-warning mb-0">Колонки To Do List не найдены. Выполните миграцию <code>admin_todo_board.sql</code>.</div>
	<?php else: ?>
		<div class="admin-todo-board" id="todoBoard"></div>
	<?php endif; ?>

	<div class="modal fade" id="todoTaskModal" tabindex="-1" aria-labelledby="todoTaskModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="todoTaskForm">
					<div class="modal-header">
						<h2 class="modal-title h5" id="todoTaskModalTitle">Задача</h2>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
					</div>
					<div class="modal-body">
						<input type="hidden" id="todoTaskId" value="">
						<input type="hidden" id="todoTaskColumnId" value="">
						<div class="mb-3">
							<label class="form-label" for="todoTaskTitle">Название</label>
							<input type="text" class="form-control" id="todoTaskTitle" maxlength="255" required>
						</div>
						<div class="mb-0">
							<label class="form-label" for="todoTaskDescription">Описание</label>
							<textarea class="form-control" id="todoTaskDescription" rows="6"></textarea>
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
		tasks: Array.isArray(initial.tasks) ? initial.tasks : [],
	};

	const modalEl = document.getElementById('todoTaskModal');
	const formEl = document.getElementById('todoTaskForm');
	const titleInput = document.getElementById('todoTaskTitle');
	const descriptionInput = document.getElementById('todoTaskDescription');
	const taskIdInput = document.getElementById('todoTaskId');
	const columnIdInput = document.getElementById('todoTaskColumnId');
	const modalTitle = document.getElementById('todoTaskModalTitle');
	const deleteBtn = document.getElementById('todoTaskDelete');
	const errorEl = document.getElementById('todoTaskError');

	let modalInstance = null;
	let dragTaskId = 0;
	let suppressClick = false;
	let modalBackdrop = null;

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

	const getTasksForColumn = (columnId) => state.tasks.filter((task) => Number(task.column_id) === Number(columnId));

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
				value.forEach((item) => body.append(key + '[]', String(item)));
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

	const openCreateModal = (columnId) => {
		clearError();
		taskIdInput.value = '';
		columnIdInput.value = String(columnId);
		titleInput.value = '';
		descriptionInput.value = '';
		modalTitle.textContent = 'Новая задача';
		deleteBtn.classList.add('d-none');
		showModal();
		window.setTimeout(() => titleInput.focus(), 150);
	};

	const openEditModal = (taskId) => {
		const task = state.tasks.find((item) => Number(item.id) === Number(taskId));
		if (!task) {
			return;
		}

		clearError();
		taskIdInput.value = String(task.id);
		columnIdInput.value = String(task.column_id);
		titleInput.value = task.title || '';
		descriptionInput.value = task.description || '';
		modalTitle.textContent = 'Задача';
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
				? tasks.map((task) => `
					<article
						class="admin-todo-task"
						draggable="true"
						data-task-id="${Number(task.id)}"
						style="--todo-color: ${escapeHtml(column.color)}"
					>
						<p class="admin-todo-task__title">${escapeHtml(task.title)}</p>
					</article>
				`).join('')
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
		const columnOrders = state.columns.map((column) => {
			const listEl = boardEl.querySelector('[data-column-list="' + column.id + '"]');
			return {
				columnId: Number(column.id),
				orderedIds: listEl ? collectOrderedIds(listEl) : [],
			};
		});

		syncBoardFromDom();

		for (const columnOrder of columnOrders) {
			await postForm('/admin/development/todo/tasks/reorder/', {
				column_id: columnOrder.columnId,
				ordered_ids: columnOrder.orderedIds,
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
		const columnId = Number(columnIdInput.value || 0);
		const title = titleInput.value.trim();
		const description = descriptionInput.value.trim();

		if (!title) {
			showError('Введите название задачи.');
			return;
		}

		try {
			if (taskId > 0) {
				const result = await postForm('/admin/development/todo/tasks/' + taskId + '/update/', {
					title,
					description,
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
				});
				if (result.task) {
					state.tasks.push(result.task);
				}
			}

			hideModal();
			render();
		} catch (error) {
			showError(error.message || 'Не удалось сохранить задачу.');
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
			state.tasks = state.tasks.filter((task) => Number(task.id) !== taskId);
			hideModal();
			render();
		} catch (error) {
			showError(error.message || 'Не удалось удалить задачу.');
		}
	});

	render();
})();
</script>
