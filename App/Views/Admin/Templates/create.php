<?php
/** @var array $data */

$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
?>

<section class="admin-template-create">
	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h1 class="h4 mb-0">Создание шаблона</h1>
				<a href="/admin/settings/templates/" class="btn btn-outline-secondary btn-sm">Назад к шаблонам</a>
			</div>

			<?php if ($flash !== null): ?>
				<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
					<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
				</div>
			<?php endif; ?>

			<form action="/admin/settings/templates/create/" method="post">
				<div class="mb-3">
					<label for="templateCode" class="form-label">Код папки</label>
					<input type="text" class="form-control" id="templateCode" name="code" required pattern="[a-zA-Z0-9_-]+" placeholder="MyTemplate">
					<div class="form-text">Только латинские буквы, цифры, дефис и нижнее подчеркивание.</div>
				</div>

				<div class="mb-3">
					<label for="templateName" class="form-label">Название</label>
					<input type="text" class="form-control" id="templateName" name="name" required>
				</div>

				<div class="mb-3">
					<label for="templateDescription" class="form-label">Описание</label>
					<textarea class="form-control" id="templateDescription" name="description" rows="5"></textarea>
				</div>

				<button type="submit" class="btn btn-primary">Создать</button>
			</form>
		</div>
	</div>
</section>
