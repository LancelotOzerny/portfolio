<?php
/* @var array $data */

$item = $data['item'] ?? null;
$usageCount = (int) ($data['usageCount'] ?? 0);
$saved = (bool) ($data['saved'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
$roleId = (int) ($item->id ?? 0);
?>

<section class="admin-user-roles-edit">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Пользователи → Роли</div>
			<h1 class="h3 mb-0">Редактирование роли</h1>
		</div>
		<a class="btn btn-outline-secondary" href="/admin/users/roles/">К списку</a>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Роль сохранена.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<?php if ($usageCount > 0): ?>
				<div class="alert alert-light border mb-4">
					Роль назначена <?= $usageCount ?> <?= $usageCount === 1 ? 'пользователю' : 'пользователям' ?>.
				</div>
			<?php endif; ?>

			<?php
			$action = '/admin/users/roles/' . $roleId . '/';
			include __DIR__ . '/_role-form.php';
			?>
		</div>
	</div>

	<div class="card border-0 shadow-sm border-danger-subtle">
		<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
			<div>
				<div class="fw-semibold text-danger">Удаление роли</div>
				<div class="small text-secondary">
					<?php if ($usageCount > 0): ?>
						Сначала снимите роль со всех пользователей.
					<?php else: ?>
						Роль будет удалена без возможности восстановления.
					<?php endif; ?>
				</div>
			</div>
			<form action="/admin/users/roles/<?= $roleId ?>/delete/" method="post" onsubmit="return confirm('Удалить роль «<?= htmlspecialchars((string) ($item->role ?? ''), ENT_QUOTES) ?>»?');">
				<button class="btn btn-outline-danger" type="submit" <?= $usageCount > 0 ? 'disabled' : '' ?>>Удалить роль</button>
			</form>
		</div>
	</div>
</section>
