<?php
/* @var array $data */

$items = $data['items'] ?? [];
$saved = (bool) ($data['saved'] ?? false);
$deleted = (bool) ($data['deleted'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
?>

<section class="admin-tags">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Контент</div>
			<h1 class="h3 mb-0">Теги</h1>
		</div>
		<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createTagModal">Добавить тег</button>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Тег сохранён.</div>
	<?php endif; ?>
	<?php if ($deleted): ?>
		<div class="alert alert-success">Тег удалён.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if (empty($items)): ?>
		<div class="alert alert-light border">Тегов пока нет.</div>
	<?php else: ?>
		<div class="card border-0 shadow-sm">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th scope="col">Название</th>
							<th scope="col">Фильтр</th>
							<th scope="col">Проектов</th>
							<th scope="col" class="text-end">Действия</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($items as $tag): ?>
							<?php
							$tagId = (int) ($tag->id ?? 0);
							$isFilter = (int) ($tag->use_as_filter ?? 0) === 1;
							?>
							<tr>
								<td class="fw-semibold"><?= htmlspecialchars((string) ($tag->name ?? '')) ?></td>
								<td>
									<span class="badge <?= $isFilter ? 'text-bg-primary' : 'text-bg-secondary' ?>">
										<?= $isFilter ? 'Да' : 'Нет' ?>
									</span>
								</td>
								<td><?= (int) ($tag->projects_count ?? 0) ?></td>
								<td class="text-end">
									<a class="btn btn-outline-primary btn-sm" href="/admin/content/tags/<?= $tagId ?>/">Изменить</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>
</section>

<div class="modal fade" id="createTagModal" tabindex="-1" aria-labelledby="createTagModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h2 class="modal-title fs-5" id="createTagModalLabel">Новый тег</h2>
				<button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Закрыть"></button>
			</div>
			<div class="modal-body">
				<?php
				$item = null;
				$action = '/admin/content/tags/create/';
				include __DIR__ . '/_tag-form.php';
				?>
			</div>
		</div>
	</div>
</div>
