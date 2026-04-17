<?php
/* @var array $data */

$project = $data['project'] ?? null;
$tags = $data['tags'] ?? [];
$allTags = $data['allTags'] ?? [];
$links = $data['links'] ?? [];
$projectInfo = $data['projectInfo'] ?? [];
$saveSuccess = (bool) ($data['saveSuccess'] ?? false);
$saveError = trim((string) ($data['saveError'] ?? ''));

if ($project === null) {
	echo '<div class="alert alert-danger">Проект не найден.</div>';
	return;
}

$isActive = (int) ($project->active ?? 0) === 1;
$previewImageUrl = trim((string) ($project->preview_image_url ?? ''));
$detailImageUrl = trim((string) ($project->detail_image_url ?? ''));

$selectedTagIds = [];
foreach ($tags as $tag) {
	$tagId = (int) ($tag->id ?? $tag->tag_id ?? 0);
	if ($tagId > 0) {
		$selectedTagIds[] = $tagId;
	}
}
$selectedTagIds = array_values(array_unique($selectedTagIds));
?>

<section class="admin-project-detail">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<h1 class="h3 mb-1">Редактирование проекта #<?= (int) ($project->id ?? 0) ?></h1>
			<p class="text-secondary mb-0"><?= htmlspecialchars((string) ($project->name ?? 'Без названия')) ?></p>
		</div>
		<a href="/admin/projects/" class="btn btn-outline-secondary">К списку проектов</a>
	</div>

	<?php if ($saveSuccess): ?>
		<div class="alert alert-success">Изменения сохранены.</div>
	<?php endif; ?>

	<?php if ($saveError !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($saveError) ?></div>
	<?php endif; ?>

	<form action="/admin/projects/<?= (int) ($project->id ?? 0) ?>/" method="post" enctype="multipart/form-data" class="card border-0 shadow-sm">
		<div class="card-header bg-white border-bottom-0 pb-0">
			<ul class="nav nav-tabs card-header-tabs" id="project-tabs" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="tab-main-link" data-bs-toggle="tab" data-bs-target="#tab-main" type="button" role="tab">Основная информация</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="tab-preview-link" data-bs-toggle="tab" data-bs-target="#tab-preview" type="button" role="tab">Preview</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="tab-detail-link" data-bs-toggle="tab" data-bs-target="#tab-detail" type="button" role="tab">Detail</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="tab-other-link" data-bs-toggle="tab" data-bs-target="#tab-other" type="button" role="tab">Другое</button>
				</li>
			</ul>
		</div>

		<div class="card-body">
			<div class="tab-content pt-2" id="project-tabs-content">
				<div class="tab-pane fade show active" id="tab-main" role="tabpanel" aria-labelledby="tab-main-link">
					<div class="row g-3">
						<div class="col-12 col-md-4">
							<label class="form-label">ID</label>
							<input type="text" class="form-control" value="<?= (int) ($project->id ?? 0) ?>" readonly>
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">changed_at</label>
							<input type="text" class="form-control" value="<?= htmlspecialchars((string) ($project->changed_at ?? '')) ?>" readonly>
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">created_at</label>
							<input type="text" class="form-control" value="<?= htmlspecialchars((string) ($project->created_at ?? '')) ?>" readonly>
						</div>

						<div class="col-12">
							<label class="form-label">Название проекта</label>
							<input type="text" name="name" class="form-control" value="<?= htmlspecialchars((string) ($project->name ?? '')) ?>">
						</div>

						<div class="col-12">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" id="active" name="active" <?= $isActive ? 'checked' : '' ?>>
								<label class="form-check-label" for="active">Активный проект</label>
							</div>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-preview" role="tabpanel" aria-labelledby="tab-preview-link">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label">Preview изображение</label>
							<?php if ($previewImageUrl !== ''): ?>
								<div class="border rounded p-3">
									<button type="button" class="btn p-0 border-0 bg-transparent project-image-trigger d-flex justify-content-center w-100" data-target-input="preview_image_file" title="Нажмите, чтобы заменить изображение">
										<img id="preview-image-preview" src="<?= htmlspecialchars($previewImageUrl) ?>" alt="Preview изображение" class="img-fluid rounded shadow-sm" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; cursor: pointer; display: block; margin: 0 auto;">
									</button>
									<div class="form-text mt-2">Изображение загружено. Нажмите на него, чтобы выбрать новое.</div>
								</div>
								<input type="file" id="preview_image_file" name="preview_image_file" accept="image/*" class="form-control mt-2 d-none project-image-input" data-preview-image="preview-image-preview">
								<input type="hidden" name="preview_image_url_existing" value="<?= htmlspecialchars($previewImageUrl) ?>">
							<?php else: ?>
								<div class="border rounded p-3 bg-light-subtle">
									<div class="text-secondary mb-2">Изображение не загружено</div>
									<input type="file" id="preview_image_file" name="preview_image_file" accept="image/*" class="form-control project-image-input" data-preview-image="preview-image-preview">
									<img id="preview-image-preview" src="" alt="Preview изображение" class="img-fluid rounded shadow-sm mt-3 d-none" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto;">
								</div>
							<?php endif; ?>
						</div>

						<div class="col-12">
							<label class="form-label">Preview text</label>
							<textarea name="preview_text" rows="6" class="form-control"><?= htmlspecialchars((string) ($project->preview_text ?? '')) ?></textarea>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-detail" role="tabpanel" aria-labelledby="tab-detail-link">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label">Detail изображение</label>
							<?php if ($detailImageUrl !== ''): ?>
								<div class="border rounded p-3">
									<button type="button" class="btn p-0 border-0 bg-transparent project-image-trigger d-flex justify-content-center w-100" data-target-input="detail_image_file" title="Нажмите, чтобы заменить изображение">
										<img id="detail-image-preview" src="<?= htmlspecialchars($detailImageUrl) ?>" alt="Detail изображение" class="img-fluid rounded shadow-sm" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; cursor: pointer; display: block; margin: 0 auto;">
									</button>
									<div class="form-text mt-2">Изображение загружено. Нажмите на него, чтобы выбрать новое.</div>
								</div>
								<input type="file" id="detail_image_file" name="detail_image_file" accept="image/*" class="form-control mt-2 d-none project-image-input" data-preview-image="detail-image-preview">
								<input type="hidden" name="detail_image_url_existing" value="<?= htmlspecialchars($detailImageUrl) ?>">
							<?php else: ?>
								<div class="border rounded p-3 bg-light-subtle">
									<div class="text-secondary mb-2">Изображение не загружено</div>
									<input type="file" id="detail_image_file" name="detail_image_file" accept="image/*" class="form-control project-image-input" data-preview-image="detail-image-preview">
									<img id="detail-image-preview" src="" alt="Detail изображение" class="img-fluid rounded shadow-sm mt-3 d-none" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto;">
								</div>
							<?php endif; ?>
						</div>

						<div class="col-12">
							<label class="form-label">Detail text</label>
							<textarea id="detail_text_editor_input" name="detail_text" rows="10" class="form-control d-none"><?= htmlspecialchars((string) ($project->detail_text ?? '')) ?></textarea>
							<div id="detail_text_editor" class="bg-white border rounded" style="min-height: 420px;"></div>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-other" role="tabpanel" aria-labelledby="tab-other-link">
					<div class="mb-4">
						<h3 class="h6 mb-3">Теги проекта</h3>
						<?php if (empty($allTags)): ?>
							<div class="alert alert-warning mb-0">Список тегов пуст.</div>
						<?php else: ?>
							<div class="row g-2">
								<?php foreach ($allTags as $tag): ?>
									<?php
									$tagId = (int) ($tag->id ?? 0);
									$tagName = trim((string) ($tag->name ?? ''));
									$checked = in_array($tagId, $selectedTagIds, true);
									?>
									<div class="col-12 col-sm-6 col-lg-3">
										<label class="form-check border rounded px-3 py-2 h-100 d-flex align-items-center gap-2">
											<input class="form-check-input mt-0" type="checkbox" name="project_tag_ids[]" value="<?= $tagId ?>" <?= $checked ? 'checked' : '' ?>>
											<span><?= htmlspecialchars($tagName !== '' ? $tagName : ('Тег #' . $tagId)) ?></span>
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="mb-4">
						<h3 class="h6 mb-3">Ссылки проекта</h3>
						<?php
						$renderLinks = $links;
						if (empty($renderLinks)) {
							$renderLinks = [(object) ['name' => '', 'link' => '']];
						}
						?>
						<div id="project-links-list" class="d-grid gap-2" data-next-index="<?= count($renderLinks) ?>">
							<?php foreach ($renderLinks as $index => $link): ?>
								<div class="border rounded p-2">
									<div class="d-flex align-items-center gap-2 flex-wrap">
										<span class="small fw-semibold text-secondary">[<?= $index + 1 ?>]</span>
										<input type="text" name="links[<?= $index ?>][name]" class="form-control form-control-sm" style="max-width: 260px;" placeholder="название" value="<?= htmlspecialchars((string) ($link->name ?? '')) ?>">
										<span class="small text-secondary">[</span>
										<input type="text" name="links[<?= $index ?>][link]" class="form-control form-control-sm" style="min-width: 280px; max-width: 520px;" placeholder="ссылка" value="<?= htmlspecialchars((string) ($link->link ?? '')) ?>">
										<span class="small text-secondary">]</span>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<button id="add-project-link" type="button" class="btn btn-outline-primary btn-sm mt-3">Добавить</button>
					</div>

					<div>
						<h3 class="h6 mb-3">Детальная информация проекта</h3>
						<?php if (empty($projectInfo)): ?>
							<div class="row g-2">
								<div class="col-12 col-md-4">
									<label class="form-label">Дата</label>
									<input type="date" name="project_info[0][date]" class="form-control" value="">
								</div>
								<div class="col-12 col-md-4">
									<label class="form-label">Время разработки</label>
									<input type="text" name="project_info[0][develop_time]" class="form-control" value="">
								</div>
								<div class="col-12 col-md-4">
									<label class="form-label">Версия</label>
									<input type="text" name="project_info[0][version]" class="form-control" value="">
								</div>
							</div>
						<?php else: ?>
							<?php foreach ($projectInfo as $index => $item): ?>
								<div class="border rounded p-3 mb-3">
									<div class="row g-2">
										<div class="col-12 col-md-3">
											<label class="form-label">ID</label>
											<input type="text" class="form-control" value="<?= (int) ($item->id ?? 0) ?>" readonly>
										</div>
										<div class="col-12 col-md-3">
											<label class="form-label">Дата</label>
											<input type="date" name="project_info[<?= $index ?>][date]" class="form-control" value="<?= htmlspecialchars((string) ($item->date ?? '')) ?>">
										</div>
										<div class="col-12 col-md-3">
											<label class="form-label">Время разработки</label>
											<input type="text" name="project_info[<?= $index ?>][develop_time]" class="form-control" value="<?= htmlspecialchars((string) ($item->develop_time ?? '')) ?>">
										</div>
										<div class="col-12 col-md-3">
											<label class="form-label">Версия</label>
											<input type="text" name="project_info[<?= $index ?>][version]" class="form-control" value="<?= htmlspecialchars((string) ($item->version ?? '')) ?>">
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="card-footer bg-white border-top d-flex justify-content-end">
			<button type="submit" class="btn btn-primary">Сохранить</button>
		</div>
	</form>
</section>

<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
	const detailEditorRoot = document.getElementById('detail_text_editor');
	const detailEditorInput = document.getElementById('detail_text_editor_input');
	const initialDetailHtml = <?= json_encode((string) ($project->detail_text ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

	if (window.Quill && detailEditorRoot && detailEditorInput) {
		const quill = new Quill('#detail_text_editor', {
			theme: 'snow',
			modules: {
				toolbar: [
					[{ header: [1, 2, 3, false] }],
					['bold', 'italic', 'underline'],
					[{ list: 'ordered' }, { list: 'bullet' }],
					['link', 'image'],
					['clean']
				]
			}
		});

		if (initialDetailHtml) {
			quill.root.innerHTML = initialDetailHtml;
		}

		const syncEditorValue = () => {
			detailEditorInput.value = quill.root.innerHTML;
		};

		syncEditorValue();
		quill.on('text-change', syncEditorValue);

		const form = detailEditorInput.closest('form');
		if (form) {
			form.addEventListener('submit', syncEditorValue);
		}
	}

	const triggerButtons = document.querySelectorAll('.project-image-trigger');
	triggerButtons.forEach((button) => {
		button.addEventListener('click', () => {
			const inputId = button.dataset.targetInput;
			const input = inputId ? document.getElementById(inputId) : null;
			if (!input) {
				return;
			}

			const shouldReplace = window.confirm('Загрузить новую картинку?');
			if (!shouldReplace) {
				return;
			}

			input.classList.remove('d-none');
			input.click();
		});
	});

	const fileInputs = document.querySelectorAll('.project-image-input');
	fileInputs.forEach((input) => {
		input.addEventListener('change', () => {
			const previewId = input.dataset.previewImage;
			const preview = previewId ? document.getElementById(previewId) : null;
			const file = input.files && input.files[0] ? input.files[0] : null;

			if (!preview || !file) {
				return;
			}

			const reader = new FileReader();
			reader.onload = (event) => {
				preview.src = event.target && typeof event.target.result === 'string' ? event.target.result : '';
				preview.classList.remove('d-none');
			};
			reader.readAsDataURL(file);
		});
	});

	const linksList = document.getElementById('project-links-list');
	const addLinkButton = document.getElementById('add-project-link');

	if (linksList && addLinkButton) {
		addLinkButton.addEventListener('click', () => {
			const nextIndex = Number.parseInt(linksList.dataset.nextIndex || '0', 10);
			const lineNumber = nextIndex + 1;

			const row = document.createElement('div');
			row.className = 'border rounded p-2';
			row.innerHTML = `
				<div class="d-flex align-items-center gap-2 flex-wrap">
					<span class="small fw-semibold text-secondary">[${lineNumber}]</span>
					<input type="text" name="links[${nextIndex}][name]" class="form-control form-control-sm" style="max-width: 260px;" placeholder="название" value="">
					<span class="small text-secondary">[</span>
					<input type="text" name="links[${nextIndex}][link]" class="form-control form-control-sm" style="min-width: 280px; max-width: 520px;" placeholder="ссылка" value="">
					<span class="small text-secondary">]</span>
				</div>
			`;

			linksList.appendChild(row);
			linksList.dataset.nextIndex = String(nextIndex + 1);
		});
	}
});
</script>
