<?php
$roles = $roles ?? [];
?>

<form action="<?= htmlspecialchars($action) ?>" method="post" class="user-form">
	<div class="mb-3">
		<label class="form-label" for="user_login">Логин</label>
		<input class="form-control" id="user_login" name="login" type="text" required minlength="3" maxlength="64" autocomplete="off">
	</div>
	<div class="mb-3">
		<label class="form-label" for="user_password">Пароль</label>
		<input class="form-control" id="user_password" name="password" type="password" required minlength="4" maxlength="128" autocomplete="new-password">
	</div>
	<div class="mb-3">
		<label class="form-label" for="user_password_confirm">Повтор пароля</label>
		<input class="form-control" id="user_password_confirm" name="password_confirm" type="password" required minlength="4" maxlength="128" autocomplete="new-password">
	</div>
	<div class="mb-3">
		<label class="form-label" for="user_rights_id">Роль</label>
		<select class="form-select" id="user_rights_id" name="rights_id" required>
			<option value="">Выберите роль</option>
			<?php foreach ($roles as $role): ?>
				<?php
				$roleId = (int) ($role->id ?? 0);
				$roleName = (string) ($role->role ?? '');
				$roleCode = trim((string) ($role->code ?? ''));
				$roleLevel = (string) ($role->level ?? '');
				?>
				<option value="<?= $roleId ?>">
					<?= htmlspecialchars($roleName) ?><?= $roleCode !== '' ? ' [' . htmlspecialchars($roleCode) . ']' : '' ?> (уровень <?= htmlspecialchars($roleLevel) ?>)
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<button class="btn btn-primary" type="submit">Добавить</button>
</form>
