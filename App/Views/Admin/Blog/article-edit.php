<?php
/* @var array $data */

use App\Services\Blog\BlogArticlePublicationService;

$article = $data['article'] ?? null;
$topics = $data['topics'] ?? [];
$selectedTopicIds = $data['selectedTopicIds'] ?? [];
$isCreate = $article === null;
$articleId = (int) ($article->id ?? 0);
$topicId = (int) ($article->topic_id ?? 0);
$title = (string) ($article->title ?? '');
$code = (string) ($article->code ?? '');
$isEnabled = (int) ($article->enabled ?? 0) === 1;
$previewText = (string) ($article->preview_text ?? '');
$previewImagePath = trim((string) ($article->preview_image_path ?? ''));
$detailText = (string) ($article->detail_text ?? '');
$detailImagePath = trim((string) ($article->detail_image_path ?? ''));
$author = (string) ($article->author ?? '');
$saveSuccess = (bool) ($data['saveSuccess'] ?? false);
$saveError = trim((string) ($data['saveError'] ?? ''));
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
$formAction = $isCreate ? '/admin/content/blog/articles/create/' : '/admin/content/blog/articles/' . $articleId . '/';
$publicationService = new BlogArticlePublicationService();
$scheduledDatetime = !$isCreate
	? ($data['scheduledDatetime'] ?? $publicationService->getScheduledDatetime($article))
	: null;
$scheduleInputValue = $publicationService->formatForInput(
	$scheduledDatetime !== null ? $scheduledDatetime : date('Y-m-d H:i:s', strtotime('+1 day'))
);

if (empty($selectedTopicIds) && $topicId > 0) {
	$selectedTopicIds = [$topicId];
}
?>

<section class="admin-blog-article-edit">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<h1 class="h3 mb-1"><?= $isCreate ? 'Создание статьи блога' : 'Редактирование статьи #' . $articleId ?></h1>
			<p class="text-secondary mb-0"><?= htmlspecialchars($title !== '' ? $title : 'Новая статья') ?></p>
		</div>
		<a href="/admin/content/blog/articles/" class="btn btn-outline-secondary">К списку статей</a>
	</div>

	<?php if ($flash !== null): ?>
		<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>">
			<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
		</div>
	<?php endif; ?>

	<?php if ($saveSuccess): ?>
		<div class="alert alert-success">Изменения сохранены.</div>
	<?php endif; ?>

	<?php if ($saveError !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($saveError) ?></div>
	<?php endif; ?>

	<?php if (empty($topics)): ?>
		<div class="alert alert-warning">Перед созданием статьи добавьте хотя бы одну рубрику.</div>
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
				<?php if (!$isCreate): ?>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="tab-publication-link" data-bs-toggle="tab" data-bs-target="#tab-publication" type="button" role="tab">Публикация</button>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<div class="card-body">
			<div class="tab-content pt-2">
				<div class="tab-pane fade show active" id="tab-main" role="tabpanel" aria-labelledby="tab-main-link">
					<div class="row g-3">
						<?php if (!$isCreate): ?>
							<div class="col-12 col-md-3">
								<label class="form-label">ID</label>
								<input type="text" class="form-control" value="<?= $articleId ?>" readonly>
							</div>
							<div class="col-12 col-md-3">
								<label class="form-label">Кол-во просмотров</label>
								<input type="text" class="form-control" value="<?= (int) ($article->views_count ?? 0) ?>" readonly>
							</div>
							<div class="col-12 col-md-3">
								<label class="form-label">created_at</label>
								<input type="text" class="form-control" value="<?= htmlspecialchars((string) ($article->created_at ?? '')) ?>" readonly>
							</div>
							<div class="col-12 col-md-3">
								<label class="form-label">updated_at</label>
								<input type="text" class="form-control" value="<?= htmlspecialchars((string) ($article->updated_at ?? '')) ?>" readonly>
							</div>
						<?php endif; ?>

						<div class="col-12">
							<label class="form-label">Рубрики</label>
							<?php if (empty($topics)): ?>
								<div class="alert alert-warning mb-0">Рубрики не найдены.</div>
							<?php else: ?>
								<div class="row g-2">
									<?php foreach ($topics as $topic): ?>
										<?php
										$currentTopicId = (int) ($topic->id ?? 0);
										$currentTopicTitle = (string) ($topic->title ?? 'Без названия');
										$isChecked = in_array($currentTopicId, $selectedTopicIds, true);
										?>
										<div class="col-12 col-sm-6 col-xl-4">
											<label class="form-check border rounded px-3 py-2 h-100 d-flex align-items-center gap-2">
												<input class="form-check-input mt-0" type="checkbox" name="topic_ids[]" value="<?= $currentTopicId ?>" <?= $isChecked ? 'checked' : '' ?>>
												<span><?= htmlspecialchars($currentTopicTitle) ?></span>
											</label>
										</div>
									<?php endforeach; ?>
								</div>
								<div class="form-text">Первая выбранная рубрика будет сохранена как основная.</div>
							<?php endif; ?>
						</div>

						<div class="col-12">
							<label class="form-label">Название статьи</label>
							<input type="text" name="title" id="blog-article-title" class="form-control" value="<?= htmlspecialchars($title) ?>" maxlength="255" required>
						</div>

						<div class="col-12">
							<label class="form-label">Символьный код</label>
							<input
								type="text"
								name="code"
								id="blog-article-code"
								class="form-control font-monospace"
								value="<?= htmlspecialchars($code) ?>"
								maxlength="255"
								pattern="[A-Za-z0-9_-]+"
								title="Только латинские буквы, цифры, - и _"
								<?= $isCreate ? '' : 'required' ?>
							>
							<div class="form-text">Латинские буквы, цифры, "-" и "_". Заполняется автоматически из названия.</div>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label">Автор</label>
							<input type="text" name="author" class="form-control" value="<?= htmlspecialchars($author) ?>" maxlength="255">
						</div>

						<div class="col-12">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" id="enabled" name="enabled" <?= $isEnabled ? 'checked' : '' ?>>
								<label class="form-check-label" for="enabled">Активная статья</label>
							</div>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-preview" role="tabpanel" aria-labelledby="tab-preview-link">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label">Preview изображение</label>
							<?php if ($previewImagePath !== ''): ?>
								<div class="border rounded p-3">
									<button type="button" class="btn p-0 border-0 bg-transparent blog-article-image-trigger d-flex justify-content-center w-100" data-target-input="preview_image_file" title="Нажмите, чтобы заменить изображение">
										<img id="preview-image-preview" src="<?= htmlspecialchars($previewImagePath) ?>" alt="Preview изображение" class="img-fluid rounded shadow-sm" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; cursor: pointer; display: block; margin: 0 auto;">
									</button>
									<div class="form-text mt-2">Изображение загружено. Нажмите на него, чтобы выбрать новое.</div>
								</div>
								<input type="file" id="preview_image_file" name="preview_image_file" accept="image/*" class="form-control mt-2 d-none blog-article-image-input" data-preview-image="preview-image-preview">
								<input type="hidden" name="preview_image_path_existing" value="<?= htmlspecialchars($previewImagePath) ?>">
							<?php else: ?>
								<div class="border rounded p-3 bg-light-subtle">
									<div class="text-secondary mb-2">Изображение не загружено</div>
									<input type="file" id="preview_image_file" name="preview_image_file" accept="image/*" class="form-control blog-article-image-input" data-preview-image="preview-image-preview">
									<img id="preview-image-preview" src="" alt="Preview изображение" class="img-fluid rounded shadow-sm mt-3 d-none" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto;">
								</div>
							<?php endif; ?>
						</div>

						<div class="col-12">
							<label class="form-label">Preview текст</label>
							<textarea name="preview_text" rows="6" class="form-control" maxlength="500"><?= htmlspecialchars($previewText) ?></textarea>
							<div class="form-text">До 500 символов.</div>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-detail" role="tabpanel" aria-labelledby="tab-detail-link">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label">Detail изображение</label>
							<?php if ($detailImagePath !== ''): ?>
								<div class="border rounded p-3">
									<button type="button" class="btn p-0 border-0 bg-transparent blog-article-image-trigger d-flex justify-content-center w-100" data-target-input="detail_image_file" title="Нажмите, чтобы заменить изображение">
										<img id="detail-image-preview" src="<?= htmlspecialchars($detailImagePath) ?>" alt="Detail изображение" class="img-fluid rounded shadow-sm" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; cursor: pointer; display: block; margin: 0 auto;">
									</button>
									<div class="form-text mt-2">Изображение загружено. Нажмите на него, чтобы выбрать новое.</div>
								</div>
								<input type="file" id="detail_image_file" name="detail_image_file" accept="image/*" class="form-control mt-2 d-none blog-article-image-input" data-preview-image="detail-image-preview">
								<input type="hidden" name="detail_image_path_existing" value="<?= htmlspecialchars($detailImagePath) ?>">
							<?php else: ?>
								<div class="border rounded p-3 bg-light-subtle">
									<div class="text-secondary mb-2">Изображение не загружено</div>
									<input type="file" id="detail_image_file" name="detail_image_file" accept="image/*" class="form-control blog-article-image-input" data-preview-image="detail-image-preview">
									<img id="detail-image-preview" src="" alt="Detail изображение" class="img-fluid rounded shadow-sm mt-3 d-none" style="max-width: min(100%, 640px); max-height: 450px; width: auto; height: auto; object-fit: contain; display: block; margin: 0 auto;">
								</div>
							<?php endif; ?>
						</div>

						<div class="col-12">
							<label class="form-label">Детальный текст</label>
							<textarea name="detail_text" rows="14" class="form-control font-monospace" spellcheck="false"><?= htmlspecialchars($detailText) ?></textarea>
						</div>
					</div>
				</div>

				<div class="tab-pane fade" id="tab-seo" role="tabpanel" aria-labelledby="tab-seo-link">
					<?php
					$seoForm = is_array($data['seoForm'] ?? null) ? $data['seoForm'] : [];
					$previewTitle = $title;
					$previewDescription = $previewText;
					$previewImage = $previewImagePath;
					include __DIR__ . '/_seo-tab.php';
					?>
				</div>

				<?php if (!$isCreate): ?>
					<div class="tab-pane fade" id="tab-publication" role="tabpanel" aria-labelledby="tab-publication-link">
						<?php include __DIR__ . '/_publication-tab.php'; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="card-footer bg-white border-top d-flex justify-content-end">
			<button type="submit" class="btn btn-primary"><?= $isCreate ? 'Создать' : 'Сохранить' ?></button>
		</div>
	</form>

	<?php if (!$isCreate): ?>
		<form id="blog-article-publish-form" action="/admin/content/blog/articles/<?= $articleId ?>/publish/" method="post" class="d-none"></form>

		<div class="modal fade" id="blog-article-schedule-modal" tabindex="-1" aria-labelledby="blog-article-schedule-modal-label" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form action="/admin/content/blog/articles/<?= $articleId ?>/schedule/" method="post">
						<input type="hidden" name="back" value="<?= htmlspecialchars($formAction) ?>">
						<div class="modal-header">
							<h2 class="modal-title h5" id="blog-article-schedule-modal-label">Отложить публикацию</h2>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
						</div>
						<div class="modal-body">
							<label class="form-label" for="blog-article-schedule-at">Дата и время публикации</label>
							<input
								type="datetime-local"
								class="form-control"
								id="blog-article-schedule-at"
								name="published_at"
								value="<?= htmlspecialchars($scheduleInputValue) ?>"
								required
							>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Отмена</button>
							<button type="submit" class="btn btn-primary">Запланировать</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	<?php endif; ?>
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

	const titleInput = document.getElementById('blog-article-title');
	const codeInput = document.getElementById('blog-article-code');
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

	document.querySelectorAll('.blog-article-image-trigger').forEach((button) => {
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

	document.querySelectorAll('.blog-article-image-input').forEach((input) => {
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
