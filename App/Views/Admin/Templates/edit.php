<?php
/** @var array $data */

$template = is_array($data['template'] ?? null) ? $data['template'] : [];
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
$code = (string) ($template['code'] ?? '');
$logo = (string) ($template['logo'] ?? '');
?>

<section class="admin-template-edit">
	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h1 class="h4 mb-0">Редактирование шаблона</h1>
				<a href="/admin/settings/templates/" class="btn btn-outline-secondary btn-sm">Назад к шаблонам</a>
			</div>

			<?php if ($flash !== null): ?>
				<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
					<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
				</div>
			<?php endif; ?>

			<form action="/admin/settings/templates/<?= rawurlencode($code) ?>/" method="post" enctype="multipart/form-data">
				<div class="mb-3">
					<label class="form-label">Код папки</label>
					<input type="text" class="form-control" value="<?= htmlspecialchars($code) ?>" disabled>
				</div>

				<div class="mb-3">
					<label for="templateName" class="form-label">Название</label>
					<input type="text" class="form-control" id="templateName" name="name" value="<?= htmlspecialchars((string) ($template['name'] ?? '')) ?>" required>
				</div>

				<div class="mb-3">
					<label for="templateDescription" class="form-label">Описание</label>
					<textarea class="form-control" id="templateDescription" name="description" rows="5"><?= htmlspecialchars((string) ($template['description'] ?? '')) ?></textarea>
				</div>

				<div class="mb-3">
					<label for="templateLogo" class="form-label">Логотип</label>
					<div class="d-flex flex-wrap align-items-center gap-3">
						<?php if ($logo !== ''): ?>
							<img src="<?= htmlspecialchars($logo) ?>" alt="" class="rounded border" style="width: 120px; height: 84px; object-fit: contain;">
						<?php else: ?>
							<div class="d-inline-flex align-items-center justify-content-center border rounded text-secondary small" style="width: 120px; height: 84px;">Нет логотипа</div>
						<?php endif; ?>
						<div class="flex-grow-1">
							<input type="file" class="form-control" id="templateLogo" name="logo" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
							<div class="form-text">PNG или JPG. Файл будет сохранен как template_logo.png или template_logo.jpg.</div>
						</div>
					</div>
				</div>

				<button type="submit" class="btn btn-primary">Сохранить</button>
			</form>
		</div>
	</div>
</section>
