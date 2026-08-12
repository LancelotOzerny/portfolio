<?php
/* @var array $data */

$task = $data['task'] ?? [];
$schedule = is_array($task['schedule'] ?? null) ? $task['schedule'] : [];
$isEdit = !empty($task['id']);
$action = $isEdit
	? '/admin/settings/cron/' . (int) $task['id'] . '/'
	: '/admin/settings/cron/create/';
?>

<form action="<?= htmlspecialchars($action) ?>" method="post" class="row g-3">
	<div class="col-12 col-md-6">
		<label class="form-label" for="cron-name">Название</label>
		<input class="form-control" id="cron-name" name="name" type="text" required value="<?= htmlspecialchars((string) ($task['name'] ?? '')) ?>">
	</div>
	<div class="col-12 col-md-6">
		<label class="form-label" for="cron-class">Класс</label>
		<input class="form-control" id="cron-class" name="class" type="text" required placeholder="\App\Services\Example\Task" value="<?= htmlspecialchars((string) ($task['class'] ?? '')) ?>">
	</div>
	<div class="col-12">
		<label class="form-label" for="cron-description">Описание</label>
		<input class="form-control" id="cron-description" name="description" type="text" value="<?= htmlspecialchars((string) ($task['description'] ?? '')) ?>">
	</div>
	<div class="col-12 col-md-4">
		<label class="form-label" for="cron-method">Public-метод</label>
		<input class="form-control" id="cron-method" name="method" type="text" required value="<?= htmlspecialchars((string) ($task['method'] ?? '')) ?>">
	</div>
	<div class="col-12 col-md-8">
		<label class="form-label" for="cron-params">Параметры через пробел</label>
		<input class="form-control" id="cron-params" name="params" type="text" placeholder="5 5 txt" value="<?= htmlspecialchars((string) ($task['params'] ?? '')) ?>">
	</div>

	<div class="col-12">
		<label class="form-label d-block">Расписание (crontab)</label>
		<div class="row g-2">
			<div class="col-6 col-md">
				<label class="form-label small text-secondary" for="cron-minute">Минута</label>
				<input class="form-control form-control-sm" id="cron-minute" name="schedule_minute" type="text" value="<?= htmlspecialchars((string) ($schedule['minute'] ?? '*')) ?>">
			</div>
			<div class="col-6 col-md">
				<label class="form-label small text-secondary" for="cron-hour">Час</label>
				<input class="form-control form-control-sm" id="cron-hour" name="schedule_hour" type="text" value="<?= htmlspecialchars((string) ($schedule['hour'] ?? '*')) ?>">
			</div>
			<div class="col-6 col-md">
				<label class="form-label small text-secondary" for="cron-day">День</label>
				<input class="form-control form-control-sm" id="cron-day" name="schedule_day" type="text" value="<?= htmlspecialchars((string) ($schedule['day'] ?? '*')) ?>">
			</div>
			<div class="col-6 col-md">
				<label class="form-label small text-secondary" for="cron-month">Месяц</label>
				<input class="form-control form-control-sm" id="cron-month" name="schedule_month" type="text" value="<?= htmlspecialchars((string) ($schedule['month'] ?? '*')) ?>">
			</div>
			<div class="col-6 col-md">
				<label class="form-label small text-secondary" for="cron-weekday">День недели</label>
				<input class="form-control form-control-sm" id="cron-weekday" name="schedule_weekday" type="text" value="<?= htmlspecialchars((string) ($schedule['weekday'] ?? '*')) ?>">
			</div>
		</div>
		<div class="form-text">Формат crontab: <code>*</code>, <code>*/5</code>, <code>1-5</code>, <code>1,3,5</code>. День недели: 0 — воскресенье, 6 — суббота.</div>
	</div>

	<div class="col-12">
		<div class="form-check">
			<input class="form-check-input" id="cron-enabled" name="enabled" type="checkbox" value="1" <?= !isset($task['enabled']) || !empty($task['enabled']) ? 'checked' : '' ?>>
			<label class="form-check-label" for="cron-enabled">Задача активна</label>
		</div>
	</div>

	<div class="col-12 d-flex flex-wrap gap-2">
		<button class="btn btn-primary" type="submit"><?= $isEdit ? 'Сохранить' : 'Добавить задачу' ?></button>
		<?php if ($isEdit): ?>
			<a class="btn btn-outline-secondary" href="/admin/settings/cron/">Назад к списку</a>
		<?php endif; ?>
	</div>
</form>
