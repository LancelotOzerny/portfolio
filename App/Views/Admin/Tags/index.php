<?php
/* @var array $data */

$items = $data['items'] ?? [];
$saved = (bool) ($data['saved'] ?? false);
$deleted = (bool) ($data['deleted'] ?? false);
$deletedCount = (int) ($data['deletedCount'] ?? 0);
$error = trim((string) ($data['error'] ?? ''));
$csrfToken = (string) ($data['csrfToken'] ?? '');
?>

<section class="admin-tags">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Контент</div>
			<h1 class="h3 mb-0">Категории проектов</h1>
		</div>
		<button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createTagModal">Добавить категорию</button>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">Категория сохранена.</div>
	<?php endif; ?>
	<?php if ($deleted): ?>
		<div class="alert alert-success"><?= $deletedCount > 1 ? 'Категории удалены.' : 'Категория удалена.' ?></div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if (empty($items)): ?>
		<div class="alert alert-light border">Категорий пока нет.</div>
	<?php else: ?>
		<form class="card border-0 shadow-sm" action="/admin/content/tags/bulk-delete/" method="post" data-category-bulk-form>
			<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th scope="col" class="text-center" style="width: 48px;">
								<input class="form-check-input" type="checkbox" aria-label="Выбрать все категории" data-category-select-all>
							</th>
							<th scope="col">Название</th>
							<th scope="col">Проектов</th>
							<th scope="col" class="text-end">Действия</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($items as $tag): ?>
							<?php
							$tagId = (int) ($tag->id ?? 0);
							?>
							<tr>
								<td class="text-center">
									<input class="form-check-input" type="checkbox" name="category_ids[]" value="<?= $tagId ?>" aria-label="Выбрать категорию <?= htmlspecialchars((string) ($tag->name ?? ''), ENT_QUOTES) ?>" data-category-select>
								</td>
								<td class="fw-semibold"><?= htmlspecialchars((string) ($tag->name ?? '')) ?></td>
								<td><?= (int) ($tag->projects_count ?? 0) ?></td>
								<td class="text-end">
									<a class="btn btn-outline-primary btn-sm" href="/admin/content/tags/<?= $tagId ?>/">Изменить</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="card-body border-top d-flex flex-wrap align-items-center justify-content-between gap-3">
				<div class="small text-secondary" data-category-selection-status>Выберите категории для массового действия.</div>
				<button class="btn btn-outline-danger" type="submit" disabled data-category-delete onclick="return confirm('Удалить выбранные категории? Категория будет снята у связанных проектов.');">Удалить выбранные</button>
			</div>
		</form>
	<?php endif; ?>
</section>

<script>
(() => {
	const form = document.querySelector('[data-category-bulk-form]');
	if (!form) {
		return;
	}

	const selectAll = form.querySelector('[data-category-select-all]');
	const checkboxes = Array.from(form.querySelectorAll('[data-category-select]'));
	const deleteButton = form.querySelector('[data-category-delete]');
	const status = form.querySelector('[data-category-selection-status]');

	const updateState = () => {
		const selectedCount = checkboxes.filter((checkbox) => checkbox.checked).length;
		selectAll.checked = selectedCount === checkboxes.length;
		selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
		deleteButton.disabled = selectedCount === 0;
		status.textContent = selectedCount === 0
			? 'Выберите категории для массового действия.'
			: 'Выбрано категорий: ' + selectedCount + '.';
	};

	selectAll.addEventListener('change', () => {
		checkboxes.forEach((checkbox) => {
			checkbox.checked = selectAll.checked;
		});
		updateState();
	});

	checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateState));
	updateState();
})();
</script>

<div class="modal fade" id="createTagModal" tabindex="-1" aria-labelledby="createTagModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			<h2 class="modal-title fs-5" id="createTagModalLabel">Новая категория</h2>
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
