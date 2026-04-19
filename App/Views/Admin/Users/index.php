<?php
$users = $data['users'] ?? [];
?>

<section class="admin-users">
	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h1 class="h4 mb-0">Пользователи</h1>
				<a href="/admin/" class="btn btn-outline-secondary btn-sm">Назад в админку</a>
			</div>

			<?php if (empty($users)): ?>
				<div class="alert alert-secondary mb-0">Пользователи не найдены.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-sm align-middle mb-0">
						<thead>
						<tr>
							<th class="text-nowrap">ID</th>
							<th>Логин</th>
							<th>Роль</th>
							<th class="text-nowrap">Уровень прав</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($users as $user): ?>
							<?php
							$userId = (int) ($user->id ?? 0);
							$login = (string) ($user->login ?? '');
							$roleName = trim((string) ($user->role_name ?? ''));
							$roleLevel = (string) ($user->role_level ?? '');
							?>
							<tr>
								<td class="text-nowrap">[<?= $userId ?>]</td>
								<td><?= htmlspecialchars($login) ?></td>
								<td><?= htmlspecialchars($roleName !== '' ? $roleName : '—') ?></td>
								<td class="text-nowrap"><?= htmlspecialchars($roleLevel !== '' ? $roleLevel : '—') ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

