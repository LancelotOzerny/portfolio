<?php
$users = $data['users'] ?? [];
$roles = $data['roles'] ?? [];
$currentUserId = (int) ($data['currentUserId'] ?? 0);
$saved = (bool) ($data['saved'] ?? false);
$deleted = (bool) ($data['deleted'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
?>

<section class="admin-users">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Пользователи</div>
			<h1 class="h3 mb-0">Список пользователей</h1>
		</div>
		<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal" <?= empty($roles) ? 'disabled' : '' ?>>Добавить пользователя</button>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Пользователь создан.</div>
	<?php endif; ?>
	<?php if ($deleted): ?>
		<div class="alert alert-success">Пользователь удалён.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if (empty($users)): ?>
		<div class="alert alert-light border">Пользователи не найдены.</div>
	<?php else: ?>
		<div class="card border-0 shadow-sm">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th class="text-nowrap">ID</th>
							<th>Логин</th>
							<th>Роль</th>
							<th class="text-nowrap">Уровень прав</th>
							<th class="text-end">Действия</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $user): ?>
							<?php
							$userId = (int) ($user->id ?? 0);
							$login = (string) ($user->login ?? '');
							$roleName = trim((string) ($user->role_name ?? ''));
							$roleLevel = (string) ($user->role_level ?? '');
							$isCurrent = $userId === $currentUserId;
							?>
							<tr>
								<td class="text-nowrap">[<?= $userId ?>]</td>
								<td><?= htmlspecialchars($login) ?><?php if ($isCurrent): ?> <span class="badge text-bg-secondary">Вы</span><?php endif; ?></td>
								<td><?= htmlspecialchars($roleName !== '' ? $roleName : '—') ?></td>
								<td class="text-nowrap"><?= htmlspecialchars($roleLevel !== '' ? $roleLevel : '—') ?></td>
								<td class="text-end">
									<a class="btn btn-outline-primary btn-sm" href="/admin/users/<?= $userId ?>/">Открыть</a>
									<?php if (!$isCurrent): ?>
										<form action="/admin/users/<?= $userId ?>/delete/" method="post" class="d-inline" onsubmit="return confirm('Удалить пользователя «<?= htmlspecialchars($login, ENT_QUOTES) ?>»?');">
											<button class="btn btn-outline-danger btn-sm" type="submit">Удалить</button>
										</form>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>
</section>

<?php if (!empty($roles)): ?>
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h2 class="modal-title fs-5" id="createUserModalLabel">Новый пользователь</h2>
				<button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Закрыть"></button>
			</div>
			<div class="modal-body">
				<?php
				$action = '/admin/users/create/';
				include __DIR__ . '/_user-form.php';
				?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
