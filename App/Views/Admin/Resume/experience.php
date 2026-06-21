<?php
/* @var array $data */

$items = $data['items'] ?? [];
$saved = (bool) ($data['saved'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
?>

<style>
	.rich-editor { min-height: 180px; outline: none; }
	.rich-editor:empty::before { color: #6c757d; content: 'Расскажите об обязанностях и результатах'; }
</style>

<section class="admin-resume-experience">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Резюме</div>
			<h1 class="h3 mb-0">Опыт работы</h1>
		</div>
		<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createExperienceModal">Добавить место работы</button>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Опыт работы сохранён.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if (empty($items)): ?>
		<div class="alert alert-light border">Записей пока нет.</div>
	<?php else: ?>
		<div class="d-grid gap-3">
			<?php foreach ($items as $experience): ?>
				<?php
				$isCurrent = (int) ($experience->is_current ?? 0) === 1;
				$isActive = (int) ($experience->active ?? 0) === 1;
				?>
				<article class="card border-0 shadow-sm">
					<div class="card-body d-flex flex-wrap justify-content-between align-items-start gap-3">
						<div>
							<h2 class="h5 mb-1"><?= htmlspecialchars((string) ($experience->position ?? '')) ?></h2>
							<div class="text-secondary mb-2"><?= htmlspecialchars((string) ($experience->company ?? '')) ?></div>
							<div class="small text-secondary">
								<?= htmlspecialchars(substr((string) ($experience->start_date ?? ''), 0, 7)) ?> —
								<?= $isCurrent ? 'по настоящее время' : htmlspecialchars(substr((string) ($experience->end_date ?? ''), 0, 7)) ?>
							</div>
						</div>
						<div class="d-flex align-items-center gap-2">
							<span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $isActive ? 'Активен' : 'Неактивен' ?></span>
							<a class="btn btn-outline-primary btn-sm" href="/admin/resume/experience/<?= (int) ($experience->id ?? 0) ?>/">Редактировать</a>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>

<div class="modal fade" id="createExperienceModal" tabindex="-1" aria-labelledby="createExperienceModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h2 class="modal-title fs-5" id="createExperienceModalLabel">Новое место работы</h2>
				<button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Закрыть"></button>
			</div>
			<div class="modal-body">
				<?php
				$item = null;
				$action = '/admin/resume/experience/create/';
				$submitLabel = 'Добавить';
				include __DIR__ . '/_experience-form.php';
				?>
			</div>
		</div>
	</div>
</div>

<?php include __DIR__ . '/_experience-script.php'; ?>
