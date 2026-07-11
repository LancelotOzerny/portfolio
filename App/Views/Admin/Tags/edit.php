<?php
/* @var array $data */

$item = $data['item'] ?? null;
$usageCount = (int) ($data['usageCount'] ?? 0);
$saved = (bool) ($data['saved'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
$tagId = (int) ($item->id ?? 0);
?>

<section class="admin-tags-edit">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Контент → Теги</div>
			<h1 class="h3 mb-0">Редактирование тега</h1>
		</div>
		<a class="btn btn-outline-secondary" href="/admin/content/tags/">К списку</a>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Тег сохранён.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<?php if ($usageCount > 0): ?>
				<div class="alert alert-light border mb-4">
					Тег используется в <?= $usageCount ?> <?= $usageCount === 1 ? 'проекте' : 'проектах' ?>.
				</div>
			<?php endif; ?>

			<?php
			$action = '/admin/content/tags/' . $tagId . '/';
			include __DIR__ . '/_tag-form.php';
			?>
		</div>
	</div>

	<div class="card border-0 shadow-sm border-danger-subtle">
		<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
			<div>
				<div class="fw-semibold text-danger">Удаление тега</div>
				<div class="small text-secondary">Связи с проектами будут удалены автоматически.</div>
			</div>
			<form action="/admin/content/tags/<?= $tagId ?>/delete/" method="post" onsubmit="return confirm('Удалить тег «<?= htmlspecialchars((string) ($item->name ?? ''), ENT_QUOTES) ?>»?');">
				<button class="btn btn-outline-danger" type="submit">Удалить тег</button>
			</form>
		</div>
	</div>
</section>
