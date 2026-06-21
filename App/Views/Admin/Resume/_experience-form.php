<?php
/** @var object|null $item */
/** @var string $action */
/** @var string $submitLabel */

$item = $item ?? null;
$isCurrent = (int) ($item->is_current ?? 0) === 1;
$isActive = $item === null || (int) ($item->active ?? 0) === 1;
$startDate = !empty($item->start_date) ? substr((string) $item->start_date, 0, 7) : '';
$endDate = !empty($item->end_date) ? substr((string) $item->end_date, 0, 7) : '';
$editorId = 'experience-description-' . (int) ($item->id ?? 0);
?>

<form action="<?= htmlspecialchars($action) ?>" method="post" class="experience-form">
	<div class="row g-3">
		<div class="col-12 col-lg-6">
			<label class="form-label" for="position">Должность</label>
			<input class="form-control" id="position" name="position" type="text" maxlength="255" required value="<?= htmlspecialchars((string) ($item->position ?? '')) ?>" placeholder="Разработчик отдела web-проектов">
		</div>
		<div class="col-12 col-lg-6">
			<label class="form-label" for="company">Компания</label>
			<input class="form-control" id="company" name="company" type="text" maxlength="255" required value="<?= htmlspecialchars((string) ($item->company ?? '')) ?>" placeholder="INTERVOLGA">
		</div>
		<div class="col-12 col-md-6">
			<label class="form-label" for="start_date">Дата начала</label>
			<input class="form-control" id="start_date" name="start_date" type="month" required value="<?= htmlspecialchars($startDate) ?>">
		</div>
		<div class="col-12 col-md-6">
			<label class="form-label" for="end_date">Дата окончания</label>
			<input class="form-control experience-end-date" id="end_date" name="end_date" type="month" <?= $isCurrent ? 'disabled' : 'required' ?> value="<?= htmlspecialchars($endDate) ?>">
		</div>
		<div class="col-12">
			<div class="form-check form-switch">
				<input class="form-check-input experience-current-toggle" id="is_current" name="is_current" type="checkbox" <?= $isCurrent ? 'checked' : '' ?>>
				<label class="form-check-label" for="is_current">Работаю на данный момент</label>
			</div>
		</div>
		<div class="col-12">
			<div class="form-check form-switch">
				<input class="form-check-input" id="active" name="active" type="checkbox" <?= $isActive ? 'checked' : '' ?>>
				<label class="form-check-label" for="active">Активен</label>
			</div>
			<div class="form-text">Неактивная запись сохраняется в административной части, но не выводится на сайте.</div>
		</div>
		<div class="col-12">
			<label class="form-label">Описание</label>
			<div class="border rounded bg-white overflow-hidden">
				<div class="d-flex flex-wrap gap-1 border-bottom p-2 rich-editor-toolbar" role="toolbar" aria-label="Форматирование описания">
					<button type="button" class="btn btn-sm btn-outline-secondary fw-bold" data-command="bold" title="Жирный">B</button>
					<button type="button" class="btn btn-sm btn-outline-secondary fst-italic" data-command="italic" title="Курсив">I</button>
					<button type="button" class="btn btn-sm btn-outline-secondary text-decoration-underline" data-command="underline" title="Подчёркивание">U</button>
					<button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertUnorderedList" title="Маркированный список">• Список</button>
					<button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertOrderedList" title="Нумерованный список">1. Список</button>
				</div>
				<div id="<?= $editorId ?>" class="rich-editor p-3" contenteditable="true" role="textbox" aria-multiline="true"><?= (string) ($item->description ?? '') ?></div>
			</div>
			<textarea class="d-none rich-editor-input" name="description" data-editor-id="<?= $editorId ?>"><?= htmlspecialchars((string) ($item->description ?? '')) ?></textarea>
		</div>
	</div>

	<div class="d-flex justify-content-end mt-4">
		<button class="btn btn-primary" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
	</div>
</form>
