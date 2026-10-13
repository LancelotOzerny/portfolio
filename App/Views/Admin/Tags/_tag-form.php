<?php
$isEdit = isset($item) && $item !== null;
$tagName = (string) ($item->name ?? '');
$submitLabel = $isEdit ? 'Сохранить' : 'Добавить';
?>

<form action="<?= htmlspecialchars($action) ?>" method="post" class="tag-form">
	<div class="row g-3">
		<div class="col-md-8">
			<label class="form-label" for="tag_name">Название категории</label>
			<input class="form-control" id="tag_name" name="name" type="text" required maxlength="255" value="<?= htmlspecialchars($tagName) ?>">
		</div>
	</div>

	<div class="mt-3">
		<button class="btn btn-primary" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
	</div>
</form>
