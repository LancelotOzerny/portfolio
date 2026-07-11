<?php
$isEdit = isset($item) && $item !== null;
$tagName = (string) ($item->name ?? '');
$useAsFilter = (int) ($item->use_as_filter ?? 0) === 1;
$submitLabel = $isEdit ? 'Сохранить' : 'Добавить';
?>

<form action="<?= htmlspecialchars($action) ?>" method="post" class="tag-form">
	<div class="row g-3">
		<div class="col-md-8">
			<label class="form-label" for="tag_name">Название</label>
			<input class="form-control" id="tag_name" name="name" type="text" required maxlength="255" value="<?= htmlspecialchars($tagName) ?>">
		</div>
		<div class="col-md-4 d-flex align-items-end">
			<div class="form-check mb-2">
				<input class="form-check-input" id="use_as_filter" name="use_as_filter" type="checkbox" <?= $useAsFilter ? 'checked' : '' ?>>
				<label class="form-check-label" for="use_as_filter">Использовать в фильтре портфолио</label>
			</div>
		</div>
	</div>

	<div class="mt-3">
		<button class="btn btn-primary" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
	</div>
</form>
