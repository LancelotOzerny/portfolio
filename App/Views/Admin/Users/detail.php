<?php
/* @var array $data */

$user = $data['user'] ?? null;
$token = $data['token'] ?? null;
$plainToken = trim((string) ($data['plainToken'] ?? ''));
$currentUserId = (int) ($data['currentUserId'] ?? 0);
$saved = (bool) ($data['saved'] ?? false);
$error = trim((string) ($data['error'] ?? ''));

$userId = (int) ($user->id ?? 0);
$login = (string) ($user->login ?? '');
$roleName = trim((string) ($user->role_name ?? ''));
$roleCode = trim((string) ($user->role_code ?? ''));
$roleLevel = (string) ($user->role_level ?? '');
$isCurrent = $userId === $currentUserId;
$hasToken = $token !== null;
$expiresAt = $hasToken ? (string) ($token->expires_at ?? '') : '';
?>

<section class="admin-user-detail">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Пользователи → Список пользователей</div>
			<h1 class="h3 mb-0"><?= htmlspecialchars($login !== '' ? $login : 'Пользователь') ?></h1>
		</div>
		<a class="btn btn-outline-secondary" href="/admin/users/">К списку</a>
	</div>

	<?php if ($saved && $plainToken === ''): ?>
		<div class="alert alert-success">Изменения сохранены.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<h2 class="h5 mb-3">Данные пользователя</h2>
			<dl class="row mb-0">
				<dt class="col-sm-3">ID</dt>
				<dd class="col-sm-9">[<?= $userId ?>]</dd>
				<dt class="col-sm-3">Логин</dt>
				<dd class="col-sm-9"><?= htmlspecialchars($login) ?><?php if ($isCurrent): ?> <span class="badge text-bg-secondary">Вы</span><?php endif; ?></dd>
				<dt class="col-sm-3">Роль</dt>
				<dd class="col-sm-9"><?= htmlspecialchars($roleName !== '' ? $roleName : '—') ?></dd>
				<dt class="col-sm-3">Код роли</dt>
				<dd class="col-sm-9 font-monospace"><?= htmlspecialchars($roleCode !== '' ? $roleCode : '—') ?></dd>
				<dt class="col-sm-3">Уровень прав</dt>
				<dd class="col-sm-9"><?= htmlspecialchars($roleLevel !== '' ? $roleLevel : '—') ?></dd>
			</dl>
		</div>
	</div>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<h2 class="h5 mb-3">API-токен</h2>
			<p class="text-secondary">Токен действует 1 месяц. Передавайте его в заголовке <code>Authorization: Bearer …</code>.</p>

			<?php if ($hasToken): ?>
				<div class="alert alert-light border">Действует до <?= htmlspecialchars($expiresAt !== '' ? $expiresAt : '—') ?>.</div>
			<?php else: ?>
				<div class="alert alert-light border">Действующего токена нет.</div>
			<?php endif; ?>

			<?php if ($plainToken !== ''): ?>
				<div class="alert alert-success">
					<div class="fw-semibold mb-2">Токен. Скопируйте его сейчас.</div>
					<input class="form-control font-monospace" type="text" readonly value="<?= htmlspecialchars($plainToken) ?>" onclick="this.select()">
				</div>
			<?php endif; ?>

			<div class="d-flex flex-wrap gap-2">
				<?php if (!$hasToken): ?>
					<form action="/admin/users/<?= $userId ?>/token/generate/" method="post" class="mb-0">
						<button class="btn btn-primary" type="submit">Сгенерировать токен</button>
					</form>
				<?php else: ?>
					<button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#revealTokenModal">Получить токен</button>
					<form action="/admin/users/<?= $userId ?>/token/regenerate/" method="post" class="mb-0" onsubmit="return confirm('Текущий токен перестанет действовать. Продолжить?');">
						<button class="btn btn-outline-secondary" type="submit">Перегенерировать токен</button>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php if (!$isCurrent): ?>
		<div class="card border-0 shadow-sm border-danger-subtle">
			<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
				<div>
					<div class="fw-semibold text-danger">Удаление пользователя</div>
					<div class="small text-secondary">Токены пользователя будут отозваны.</div>
				</div>
				<form action="/admin/users/<?= $userId ?>/delete/" method="post" onsubmit="return confirm('Удалить пользователя «<?= htmlspecialchars($login, ENT_QUOTES) ?>»?');">
					<button class="btn btn-outline-danger" type="submit">Удалить пользователя</button>
				</form>
			</div>
		</div>
	<?php endif; ?>
</section>

<?php if ($hasToken): ?>
<div class="modal fade" id="revealTokenModal" tabindex="-1" aria-labelledby="revealTokenModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="/admin/users/<?= $userId ?>/token/reveal/" method="post">
				<div class="modal-header">
					<h2 class="modal-title fs-5" id="revealTokenModalLabel">Получить токен</h2>
					<button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Закрыть"></button>
				</div>
				<div class="modal-body">
					<p class="text-secondary">Введите пароль текущего администратора, чтобы показать действующий токен.</p>
					<label class="form-label" for="reveal_password">Пароль</label>
					<input class="form-control" id="reveal_password" name="password" type="password" required autocomplete="current-password">
				</div>
				<div class="modal-footer">
					<button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Отмена</button>
					<button class="btn btn-primary" type="submit">Показать токен</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>
