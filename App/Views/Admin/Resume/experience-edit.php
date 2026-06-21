<?php
/* @var array $data */

$item = $data['item'] ?? null;
$saved = (bool) ($data['saved'] ?? false);
$error = trim((string) ($data['error'] ?? ''));

if ($item === null) {
	echo '<div class="alert alert-danger">Запись не найдена.</div>';
	return;
}
?>

<style>
	.rich-editor { min-height: 260px; outline: none; }
	.rich-editor:empty::before { color: #6c757d; content: 'Расскажите об обязанностях и результатах'; }
</style>

<section class="admin-resume-experience-edit">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Резюме → Опыт работы</div>
			<h1 class="h3 mb-0">Редактирование записи #<?= (int) ($item->id ?? 0) ?></h1>
		</div>
		<a class="btn btn-outline-secondary" href="/admin/resume/experience/">К списку</a>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Изменения сохранены.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<?php
			$action = '/admin/resume/experience/' . (int) ($item->id ?? 0) . '/';
			$submitLabel = 'Сохранить';
			include __DIR__ . '/_experience-form.php';
			?>
		</div>
	</div>
</section>

<?php include __DIR__ . '/_experience-script.php'; ?>
