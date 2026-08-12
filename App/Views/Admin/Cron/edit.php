<?php
/* @var array $data */

$task = $data['task'] ?? [];
$error = trim((string) ($data['error'] ?? ''));
$taskId = (int) ($task['id'] ?? 0);
?>

<section class="admin-cron-edit">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Настройки / Cron задачи</div>
			<h1 class="h3 mb-0">Редактирование задачи #<?= $taskId ?></h1>
		</div>
		<a class="btn btn-outline-secondary btn-sm" href="/admin/settings/cron/">Назад к списку</a>
	</div>

	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm">
		<div class="card-body">
			<?php include __DIR__ . '/_form.php'; ?>
		</div>
	</div>
</section>
