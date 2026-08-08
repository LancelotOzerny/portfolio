<?php
$updateResult = $data['updateResult'] ?? null;
$isSuccess = is_array($updateResult) && !empty($updateResult['success']);
$message = is_array($updateResult) ? (string) ($updateResult['message'] ?? '') : '';
$output = is_array($updateResult) ? trim((string) ($updateResult['output'] ?? '')) : '';
$commitMessage = is_array($updateResult) ? (string) ($updateResult['commitMessage'] ?? '') : '';
if ($commitMessage === '') {
	$commitMessage = 'Изменения от ' . date('d.m.Y');
}
?>

<section class="admin-repository">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
		<h1 class="h4 mb-0">Репозиторий</h1>
		<a href="/admin/" class="btn btn-outline-secondary btn-sm">Назад в админку</a>
	</div>

	<p class="text-secondary mb-4">Обновление рабочей копии проекта из ветки main и сохранение текущих правок с отправкой через git push.</p>

	<?php if ($message !== ''): ?>
		<div class="alert <?= $isSuccess ? 'alert-success' : 'alert-danger' ?>" role="alert">
			<?= htmlspecialchars($message) ?>
		</div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body p-4">
			<h2 class="h5 mb-3">Обновление</h2>
			<form action="/admin/development/repository/update/" method="post" class="mb-0">
				<button type="submit" class="btn btn-primary">
					Обновить репозиторий
				</button>
			</form>
		</div>
	</div>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body p-4">
			<h2 class="h5 mb-3">Сохранение изменений</h2>
			<form action="/admin/development/repository/save/" method="post" class="mb-0">
				<label for="commit-message" class="form-label">Текст коммита</label>
				<textarea
					id="commit-message"
					name="commit_message"
					class="form-control mb-3"
					rows="4"
					required
					placeholder="Кратко опишите изменения"
				><?= htmlspecialchars($commitMessage) ?></textarea>
				<button type="submit" class="btn btn-outline-primary">
					Создать и отправить
				</button>
			</form>
		</div>
	</div>

	<?php if ($output !== ''): ?>
		<div class="card border-0 shadow-sm">
			<div class="card-body p-4">
				<p class="small text-secondary mb-2">Результат команды:</p>
				<pre class="bg-dark text-light p-3 rounded-2 mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($output) ?></pre>
			</div>
		</div>
	<?php endif; ?>
</section>
