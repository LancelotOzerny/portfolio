<?php
/** @var array $task */
/** @var int|string $index */
/** @var bool $isTemplate */

$subtask = $task ?? [];
$index = (string) ($index ?? '__INDEX__');
$isTemplate = !empty($isTemplate);
$rowClass = $isTemplate ? 'cron-subtask-row cron-subtask-row--template d-none' : 'cron-subtask-row';
?>

<div class="<?= $rowClass ?> border rounded p-3 mb-3" data-subtask-row>
	<div class="d-flex justify-content-between align-items-center mb-3">
		<strong>Подзадача</strong>
		<button class="btn btn-outline-danger btn-sm" type="button" data-remove-subtask>Удалить</button>
	</div>

	<div class="row g-3">
		<div class="col-12 col-md-6">
			<label class="form-label">Название</label>
			<input class="form-control" name="subtasks[<?= htmlspecialchars($index) ?>][name]" type="text" value="<?= htmlspecialchars((string) ($subtask['name'] ?? '')) ?>">
		</div>
		<div class="col-12 col-md-6">
			<label class="form-label">Класс</label>
			<input class="form-control" name="subtasks[<?= htmlspecialchars($index) ?>][class]" type="text" value="<?= htmlspecialchars((string) ($subtask['class'] ?? '')) ?>">
		</div>
		<div class="col-12">
			<label class="form-label">Описание</label>
			<input class="form-control" name="subtasks[<?= htmlspecialchars($index) ?>][description]" type="text" value="<?= htmlspecialchars((string) ($subtask['description'] ?? '')) ?>">
		</div>
		<div class="col-12 col-md-4">
			<label class="form-label">Public-метод</label>
			<input class="form-control" name="subtasks[<?= htmlspecialchars($index) ?>][method]" type="text" value="<?= htmlspecialchars((string) ($subtask['method'] ?? '')) ?>">
		</div>
		<div class="col-12 col-md-8">
			<label class="form-label">Параметры через пробел</label>
			<input class="form-control" name="subtasks[<?= htmlspecialchars($index) ?>][params]" type="text" value="<?= htmlspecialchars((string) ($subtask['params'] ?? '')) ?>">
		</div>
		<div class="col-12 d-flex flex-wrap gap-3">
			<div class="form-check">
				<input class="form-check-input" name="subtasks[<?= htmlspecialchars($index) ?>][important]" type="checkbox" value="1" <?= !empty($subtask['important']) ? 'checked' : '' ?>>
				<label class="form-check-label">Важно</label>
			</div>
			<div class="form-check">
				<input class="form-check-input" name="subtasks[<?= htmlspecialchars($index) ?>][urgent]" type="checkbox" value="1" <?= !empty($subtask['urgent']) ? 'checked' : '' ?>>
				<label class="form-check-label">Срочно</label>
			</div>
			<div class="form-check">
				<input class="form-check-input" name="subtasks[<?= htmlspecialchars($index) ?>][enabled]" type="checkbox" value="1" <?= !isset($subtask['enabled']) || !empty($subtask['enabled']) ? 'checked' : '' ?>>
				<label class="form-check-label">Активна</label>
			</div>
		</div>
	</div>
</div>
