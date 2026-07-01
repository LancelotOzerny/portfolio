<?php
$updateResult = $data['updateResult'] ?? null;
$isSuccess = is_array($updateResult) && !empty($updateResult['success']);
$message = is_array($updateResult) ? (string) ($updateResult['message'] ?? '') : '';
$output = is_array($updateResult) ? trim((string) ($updateResult['output'] ?? '')) : '';
?>

<section class="admin-repository">
	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h1 class="h4 mb-0">Репозиторий</h1>
				<a href="/admin/" class="btn btn-outline-secondary btn-sm">Назад в админку</a>
			</div>

			<p class="text-secondary mb-4">Обновление рабочей копии проекта из ветки main.</p>

			<?php if ($message !== ''): ?>
				<div class="alert <?= $isSuccess ? 'alert-success' : 'alert-danger' ?>" role="alert">
					<?= htmlspecialchars($message) ?>
				</div>
			<?php endif; ?>

			<form action="/admin/settings/repository/update/" method="post" class="mb-0">
				<button type="submit" class="btn btn-primary">
					Обновить репозиторий
				</button>
			</form>

			<?php if ($output !== ''): ?>
				<hr>
				<p class="small text-secondary mb-2">Результат команды:</p>
				<pre class="bg-dark text-light p-3 rounded-2 mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($output) ?></pre>
			<?php endif; ?>
		</div>
	</div>
</section>
