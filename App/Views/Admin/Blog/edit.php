<?php
/* @var array $data */

$topic = $data['topic'] ?? null;
$isCreate = $topic === null;
$topicId = (int) ($topic->id ?? 0);
$title = (string) ($topic->title ?? '');
$code = (string) ($topic->code ?? '');
$description = (string) ($topic->preview_text ?? '');
$isEnabled = (int) ($topic->enabled ?? 0) === 1;
$imagePath = trim((string) ($topic->image_path ?? ''));
$detailText = (string) ($topic->detail_text ?? '');
$detailImagePath = trim((string) ($topic->detail_image_path ?? ''));
$saveSuccess = (bool) ($data['saveSuccess'] ?? false);
$saveError = trim((string) ($data['saveError'] ?? ''));
$formAction = $isCreate ? '/admin/content/blog/rubrics/create/' : '/admin/content/blog/rubrics/' . $topicId . '/';
?>

<section class="admin-blog-topic-edit">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<h1 class="h3 mb-1"><?= $isCreate ? 'Создание рубрики блога' : 'Редактирование рубрики #' . $topicId ?></h1>
			<p class="text-secondary mb-0"><?= htmlspecialchars($title !== '' ? $title : 'Новая рубрика') ?></p>
		</div>
		<a href="/admin/content/blog/rubrics/" class="btn btn-outline-secondary">К списку рубрик</a>
	</div>

	<?php if ($saveSuccess): ?>
		<div class="alert alert-success">Изменения сохранены.</div>
	<?php endif; ?>

	<?php if ($saveError !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($saveError) ?></div>
	<?php endif; ?>

	<form action="<?= htmlspecialchars($formAction) ?>" method="post" enctype="multipart/form-data" class="card border-0 shadow-sm">
		<div class="card-header bg-white border-bottom-0 pb-0">
			<ul class="nav nav-tabs card-header-tabs" role="tablist">
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
					<button class="nav-link" id="tab-seo-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button" role="tab">SEO</button>
				</li>
			</ul>
		</div>

		<div class="card-body">
			<div class="tab-content pt-2">
				<div class="tab-pane fade show active" id="tab-main" role="tabpanel" aria-labelledby="tab-main-link">
					<div class="row g-3">
						<?php if (!$isCreate): ?>
							<div class="col-12 col-md-4">
								<label class="form-label">ID</label>
								<input type="text" class="form-control" value="<?= $topicId ?>" readonly>
							</div>
							<div class="col-12 col-md-4">
								<label class="form-label">created_at</label>
								<input type="text" class="form-control" value="<?= htmlspecialchars((string) ($topic->created_at ?? '')) ?>" readonly>
							</div>
							<div class="col-12 col-md-4">
								<label class="form-label">updated_at</label>
								<input type="text" class="form-control" value="<?= htmlspecialchars((string) ($topic->updated_at ?? '')) ?>" readonly>
							</div>
						<?php endif; ?>

						<div class="col-12">
							<label class="form-label">Название рубрики</label>
							<input type="text" name="title" id="blog-topic-title" class="form-control" value="<?= htmlspecialchars($title) ?>" maxlength="255" required>
						</div>

						<div class="col-12">
							<label class="form-label">Символьный код</label>
							<input
								type="text"
								name="code"
								id="blog-topic-code"
								class="form-control font-monospace"
								value="<?= htmlspecialchars($code) ?>"
								maxlength="255"
								pattern="[A-Za-z0-9_-]+"
								title="Только латинские буквы, цифры, - и _"
								<?= $isCreate ? '' : 'required' ?>
							>
							<div class="form-text">Латинские буквы, цифры, "-" и "_". Заполняется автоматически из названия.</div>
						</div>

						<div class="col-12">
							<label class="form-label">Preview текст</label>
							<textarea name="description" rows="6" class="form-control" maxlength="500"><?= htmlspecialchars($description) ?></textarea>
							<div class="form-text">До 500 символов.</div>
						</div>

						<div class="col-12">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" id="enabled" name="enabled" <?= $isEnabled ? 'checked' : '' ?>>
								<label class="form-check-label" for="enabled">Активная рубрика</label>
							</div>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-preview" role="tabpanel" aria-labelledby="tab-preview-link">
					<label class="form-label">Preview изображение</label>

					<?php if ($imagePath !== ''): ?>
						<div class="border rounded p-3">
							<button type="button" class="btn p-0 border-0 bg-transparent blog-topic-image-trigger d-flex justify-content-center w-100" data-target-input="image_file" title="Нажмите, чтобы заменить изображение">
								<img id="topic-image-preview" src="<?= htmlspecialchars($imagePath) ?>" alt="Preview изображение" class="img-fluid rounded shadow-sm" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; cursor: pointer; display: block; margin: 0 auto;">
							</button>
							<div class="form-text mt-2">Изображение загружено. Нажмите на него, чтобы выбрать новое.</div>
						</div>
						<input type="file" id="image_file" name="image_file" accept="image/*" class="form-control mt-2 d-none blog-topic-image-input" data-preview-image="topic-image-preview">
						<input type="hidden" name="image_path_existing" value="<?= htmlspecialchars($imagePath) ?>">
					<?php else: ?>
						<div class="border rounded p-3 bg-light-subtle">
							<div class="text-secondary mb-2">Изображение не загружено</div>
							<input type="file" id="image_file" name="image_file" accept="image/*" class="form-control blog-topic-image-input" data-preview-image="topic-image-preview">
							<img id="topic-image-preview" src="" alt="Preview изображение" class="img-fluid rounded shadow-sm mt-3 d-none" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto;">
						</div>
					<?php endif; ?>
				</div>

				<div class="tab-pane fade" id="tab-detail" role="tabpanel" aria-labelledby="tab-detail-link">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label">Detail изображение</label>

							<?php if ($detailImagePath !== ''): ?>
								<div class="border rounded p-3">
									<button type="button" class="btn p-0 border-0 bg-transparent blog-topic-image-trigger d-flex justify-content-center w-100" data-target-input="detail_image_file" title="Нажмите, чтобы заменить изображение">
										<img id="topic-detail-image-preview" src="<?= htmlspecialchars($detailImagePath) ?>" alt="Detail изображение" class="img-fluid rounded shadow-sm" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; cursor: pointer; display: block; margin: 0 auto;">
									</button>
									<div class="form-text mt-2">Изображение загружено. Нажмите на него, чтобы выбрать новое.</div>
								</div>
								<input type="file" id="detail_image_file" name="detail_image_file" accept="image/*" class="form-control mt-2 d-none blog-topic-image-input" data-preview-image="topic-detail-image-preview">
								<input type="hidden" name="detail_image_path_existing" value="<?= htmlspecialchars($detailImagePath) ?>">
							<?php else: ?>
								<div class="border rounded p-3 bg-light-subtle">
									<div class="text-secondary mb-2">Изображение не загружено</div>
									<input type="file" id="detail_image_file" name="detail_image_file" accept="image/*" class="form-control blog-topic-image-input" data-preview-image="topic-detail-image-preview">
									<img id="topic-detail-image-preview" src="" alt="Detail изображение" class="img-fluid rounded shadow-sm mt-3 d-none" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto;">
								</div>
							<?php endif; ?>
						</div>

						<div class="col-12">
							<label class="form-label">Detail текст</label>
							<textarea name="detail_text" rows="10" class="form-control font-monospace" spellcheck="false"><?= htmlspecialchars($detailText) ?></textarea>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-link">
					<?php
					$seoForm = is_array($data['seoForm'] ?? null) ? $data['seoForm'] : [];
					$previewTitle = $title;
					$previewDescription = $description;
					$previewImage = $imagePath;
					include __DIR__ . '/_seo-tab.php';
					?>
				</div>
			</div>
		</div>

		<div class="card-footer bg-white border-top d-flex justify-content-end">
			<button type="submit" class="btn btn-primary"><?= $isCreate ? 'Создать' : 'Сохранить' ?></button>
		</div>
	</form>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
	const translitMap = {
		'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh', 'з': 'z',
		'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r',
		'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
		'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
	};

	const toSymbolicCode = (value) => {
		const lower = String(value || '').toLowerCase();
		let result = '';

		for (const char of lower) {
			if (Object.prototype.hasOwnProperty.call(translitMap, char)) {
				result += translitMap[char];
				continue;
			}

			if (/[a-z0-9_-]/.test(char)) {
				result += char;
				continue;
			}

			result += '-';
		}

		return result.replace(/-+/g, '-').replace(/^[-_]+|[-_]+$/g, '');
	};

	const titleInput = document.getElementById('blog-topic-title');
	const codeInput = document.getElementById('blog-topic-code');
	let codeLocked = Boolean(codeInput && codeInput.value.trim() !== '');

	if (titleInput && codeInput) {
		titleInput.addEventListener('input', () => {
			if (codeLocked) {
				return;
			}

			codeInput.value = toSymbolicCode(titleInput.value);
		});

		codeInput.addEventListener('input', () => {
			codeLocked = true;
			codeInput.value = toSymbolicCode(codeInput.value);
		});
	}

	document.querySelectorAll('.blog-topic-image-trigger').forEach((button) => {
		button.addEventListener('click', () => {
			const inputId = button.dataset.targetInput;
			const input = inputId ? document.getElementById(inputId) : null;
			if (!input) {
				return;
			}

			if (!window.confirm('Загрузить новую картинку?')) {
				return;
			}

			input.classList.remove('d-none');
			input.click();
		});
	});

	document.querySelectorAll('.blog-topic-image-input').forEach((input) => {
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
});
</script>
