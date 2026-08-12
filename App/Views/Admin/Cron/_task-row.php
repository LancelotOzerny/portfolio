<?php
/** @var array $task */
/** @var \App\Services\Cron\CronScheduleMatcher $matcher */
/** @var string $priorityClass */
/** @var bool $isSubtask */
/** @var int $parentId */

$taskId = (int) ($task['id'] ?? 0);
$subtaskId = $isSubtask ? (int) ($task['id'] ?? 0) : 0;
$displayId = $isSubtask ? ($parentId . '.' . $subtaskId) : (string) $taskId;
$schedule = is_array($task['schedule'] ?? null) ? $task['schedule'] : [];
$enabled = !empty($task['enabled']);
$priorityLabel = $priorityClass::label($task);
$priorityRank = $priorityClass::rank($task);
$priorityBadgeClass = match ($priorityRank) {
	$priorityClass::RANK_IMPORTANT_URGENT => 'text-bg-danger',
	$priorityClass::RANK_URGENT => 'text-bg-warning',
	$priorityClass::RANK_IMPORTANT => 'text-bg-primary',
	default => 'text-bg-secondary',
};
?>

<tr class="<?= $isSubtask ? 'table-light' : '' ?>">
	<td><?= htmlspecialchars($displayId) ?></td>
	<td class="fw-semibold">
		<?php if ($isSubtask): ?>
			<span class="text-secondary me-1">↳</span>
		<?php endif; ?>
		<?= htmlspecialchars((string) ($task['name'] ?? '')) ?>
	</td>
	<td><?= htmlspecialchars((string) ($task['description'] ?? '')) ?></td>
	<td><code><?= htmlspecialchars((string) ($task['class'] ?? '')) ?></code></td>
	<td><code><?= htmlspecialchars((string) ($task['method'] ?? '')) ?></code></td>
	<td><?= htmlspecialchars((string) ($task['params'] ?? '')) ?></td>
	<td>
		<span class="badge <?= !empty($task['important']) ? 'text-bg-primary' : 'text-bg-light text-dark border' ?>">Важно</span>
		<span class="badge <?= !empty($task['urgent']) ? 'text-bg-warning' : 'text-bg-light text-dark border' ?>">Срочно</span>
		<div class="small text-secondary mt-1"><?= htmlspecialchars($priorityLabel) ?></div>
	</td>
	<td>
		<?php if (!$isSubtask): ?>
			<div class="small text-nowrap"><?= htmlspecialchars($matcher->format($schedule)) ?></div>
			<div class="small text-secondary">
				м <?= htmlspecialchars((string) ($schedule['minute'] ?? '*')) ?> /
				ч <?= htmlspecialchars((string) ($schedule['hour'] ?? '*')) ?> /
				д <?= htmlspecialchars((string) ($schedule['day'] ?? '*')) ?> /
				мес <?= htmlspecialchars((string) ($schedule['month'] ?? '*')) ?> /
				дн <?= htmlspecialchars((string) ($schedule['weekday'] ?? '*')) ?>
			</div>
		<?php else: ?>
			<span class="text-secondary small">Наследует родителя</span>
		<?php endif; ?>
	</td>
	<td>
		<span class="badge <?= $enabled ? 'text-bg-success' : 'text-bg-secondary' ?>">
			<?= $enabled ? 'Активна' : 'Выключена' ?>
		</span>
		<?php if (!$isSubtask): ?>
			<div class="small mt-1"><span class="badge <?= $priorityBadgeClass ?>"><?= htmlspecialchars($priorityLabel) ?></span></div>
		<?php endif; ?>
	</td>
	<td class="text-end">
		<?php if (!$isSubtask): ?>
			<a class="btn btn-outline-primary btn-sm" href="/admin/settings/cron/<?= $taskId ?>/">Изменить</a>
			<form action="/admin/settings/cron/<?= $taskId ?>/delete/" method="post" class="d-inline" onsubmit="return confirm('Удалить задачу?');">
				<button class="btn btn-outline-danger btn-sm" type="submit">Удалить</button>
			</form>
		<?php endif; ?>
	</td>
</tr>
