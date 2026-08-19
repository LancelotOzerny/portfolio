<?php
/* @var array $data */

$items = $data['items'] ?? [];
$saved = (bool) ($data['saved'] ?? false);
$deleted = (bool) ($data['deleted'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
?>

<section class="admin-user-roles">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Пользователи</div>
			<h1 class="h3 mb-0">Пользовательские роли</h1>
		</div>
		<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createRoleModal">Добавить роль</button>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Роль сохранена.</div>
	<?php endif; ?>
	<?php if ($deleted): ?>
		<div class="alert alert-success">Роль удалена.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if (empty($items)): ?>
		<div class="alert alert-light border">Ролей пока нет.</div>
	<?php else: ?>
		<div class="card border-0 shadow-sm">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th scope="col">Название</th>
							<th scope="col">Код</th>
							<th scope="col">Уровень прав</th>
							<th scope="col">Пользователей</th>
							<th scope="col" class="text-end">Действия</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($items as $role): ?>
							<?php $roleId = (int) ($role->id ?? 0); ?>
							<tr>
								<td class="fw-semibold"><?= htmlspecialchars((string) ($role->role ?? '')) ?></td>
								<td class="font-monospace"><?= htmlspecialchars((string) ($role->code ?? '')) ?></td>
								<td><?= (int) ($role->level ?? 0) ?></td>
								<td><?= (int) ($role->users_count ?? 0) ?></td>
								<td class="text-end">
									<a class="btn btn-outline-primary btn-sm" href="/admin/users/roles/<?= $roleId ?>/">Изменить</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>
</section>

<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h2 class="modal-title fs-5" id="createRoleModalLabel">Новая роль</h2>
				<button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Закрыть"></button>
			</div>
			<div class="modal-body">
				<?php
				$item = null;
				$action = '/admin/users/roles/create/';
				include __DIR__ . '/_role-form.php';
				?>
			</div>
		</div>
	</div>
</div>
