<?php
/* @var array $data */

$tasks = $data['tasks'] ?? [];
$matcher = $data['matcher'] ?? new \App\Services\Cron\CronScheduleMatcher();
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
			<h2 class="h5 mb-3">Список задач</h2>

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
								<th scope="col">Расписание</th>
								<th scope="col">Статус</th>
								<th scope="col" class="text-end">Действия</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($tasks as $task): ?>
								<?php
								$taskId = (int) ($task['id'] ?? 0);
								$schedule = is_array($task['schedule'] ?? null) ? $task['schedule'] : [];
								$enabled = !empty($task['enabled']);
								?>
								<tr>
									<td><?= $taskId ?></td>
									<td class="fw-semibold"><?= htmlspecialchars((string) ($task['name'] ?? '')) ?></td>
									<td><?= htmlspecialchars((string) ($task['description'] ?? '')) ?></td>
									<td><code><?= htmlspecialchars((string) ($task['class'] ?? '')) ?></code></td>
									<td><code><?= htmlspecialchars((string) ($task['method'] ?? '')) ?></code></td>
									<td><?= htmlspecialchars((string) ($task['params'] ?? '')) ?></td>
									<td>
										<div class="small text-nowrap"><?= htmlspecialchars($matcher->format($schedule)) ?></div>
										<div class="small text-secondary">
											м <?= htmlspecialchars((string) ($schedule['minute'] ?? '*')) ?> /
											ч <?= htmlspecialchars((string) ($schedule['hour'] ?? '*')) ?> /
											д <?= htmlspecialchars((string) ($schedule['day'] ?? '*')) ?> /
											мес <?= htmlspecialchars((string) ($schedule['month'] ?? '*')) ?> /
											дн <?= htmlspecialchars((string) ($schedule['weekday'] ?? '*')) ?>
										</div>
									</td>
									<td>
										<span class="badge <?= $enabled ? 'text-bg-success' : 'text-bg-secondary' ?>">
											<?= $enabled ? 'Активна' : 'Выключена' ?>
										</span>
									</td>
									<td class="text-end">
										<a class="btn btn-outline-primary btn-sm" href="/admin/settings/cron/<?= $taskId ?>/">Изменить</a>
										<form action="/admin/settings/cron/<?= $taskId ?>/delete/" method="post" class="d-inline" onsubmit="return confirm('Удалить задачу?');">
											<button class="btn btn-outline-danger btn-sm" type="submit">Удалить</button>
										</form>
									</td>
								</tr>
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
