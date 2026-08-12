<?php
/* @var array $data */

$tasks = $data['tasks'] ?? [];
$matcher = $data['matcher'] ?? new \App\Services\Cron\CronScheduleMatcher();
$priorityClass = $data['priority'] ?? \App\Services\Cron\CronTaskPriority::class;
$cronPath = (string) ($data['cronPath'] ?? '');
$saved = (bool) ($data['saved'] ?? false);
$deleted = (bool) ($data['deleted'] ?? false);
$error = trim((string) ($data['error'] ?? ''));

$cronPath = str_replace('/', DIRECTORY_SEPARATOR, $cronPath);
?>

<section class="admin-cron">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Настройки</div>
			<h1 class="h3 mb-0">Cron задачи</h1>
		</div>
		<a class="btn btn-outline-secondary btn-sm" href="/admin/settings/">Назад в настройки</a>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Задача сохранена.</div>
	<?php endif; ?>
	<?php if ($deleted): ?>
		<div class="alert alert-success">Задача удалена.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<h2 class="h5 mb-2">Crontab</h2>
			<p class="text-secondary mb-2">Добавьте в crontab выполнение каждую минуту:</p>
			<pre class="bg-light border rounded p-3 mb-0"><code>* * * * * php <?= htmlspecialchars($cronPath) ?></code></pre>
		</div>
	</div>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<h2 class="h5 mb-2">Список задач</h2>
			<p class="text-secondary small mb-3">Сортировка: важные и срочные → срочные → важные → обычные.</p>

			<?php if (empty($tasks)): ?>
				<div class="alert alert-light border mb-0">Задач пока нет.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th scope="col">#</th>
								<th scope="col">Название</th>
								<th scope="col">Описание</th>
								<th scope="col">Класс</th>
								<th scope="col">Метод</th>
								<th scope="col">Параметры</th>
								<th scope="col">Приоритет</th>
								<th scope="col">Расписание</th>
								<th scope="col">Статус</th>
								<th scope="col" class="text-end">Действия</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($tasks as $parentTask): ?>
								<?php
								$task = $parentTask;
								$isSubtask = false;
								$parentId = 0;
								include __DIR__ . '/_task-row.php';

								$parentTaskId = (int) ($parentTask['id'] ?? 0);
								foreach (is_array($parentTask['subtasks'] ?? null) ? $parentTask['subtasks'] : [] as $subtask) {
									$task = $subtask;
									$isSubtask = true;
									$parentId = $parentTaskId;
									include __DIR__ . '/_task-row.php';
								}
								?>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="card border-0 shadow-sm">
		<div class="card-body">
			<h2 class="h5 mb-3">Новая задача</h2>
			<?php
			$task = ['enabled' => true];
			include __DIR__ . '/_form.php';
			?>
		</div>
	</div>
</section>
