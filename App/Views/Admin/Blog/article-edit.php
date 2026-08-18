<?php
/* @var array $data */

use App\Services\Blog\BlogArticlePublicationService;
use App\Services\ContentEditor\ContentEditor;

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
$commentsCount = (int) ($data['commentsCount'] ?? 0);
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

$formatAdminDatetime = static function (?string $value): string {
	$value = trim((string) $value);
	if ($value === '') {
		return '—';
	}

	$timestamp = strtotime($value);

	return $timestamp === false ? $value : date('d.m.Y H:i:s', $timestamp);
};
?>

<section class="admin-blog-article-edit">
	<style>
		.admin-dashboard__section-title {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 1.25rem;
			margin: 0 0 1.5rem;
			font-size: 1.125rem;
			font-weight: 600;
			line-height: 1.2;
			text-transform: uppercase;
			letter-spacing: 0.12em;
			color: #495057;
		}

		.admin-dashboard__section-title-line {
			flex: 1 1 0;
			max-width: 120px;
			height: 1px;
			background: linear-gradient(to right, transparent, #ced4da 20%, #ced4da 80%, transparent);
		}

		.admin-dashboard__section-title-text {
			flex: 0 0 auto;
			padding: 0.35rem 1rem;
			border-top: 1px solid #dee2e6;
			border-bottom: 1px solid #dee2e6;
		}

		.admin-blog-article-meta {
			display: grid;
			grid-template-columns: max-content max-content;
			justify-content: center;
			column-gap: 1rem;
			row-gap: 0.35rem;
			margin: 0 auto 2rem;
			width: fit-content;
		}

		.admin-blog-article-meta__label {
			text-align: right;
			color: #212529;
			white-space: nowrap;
		}

		.admin-blog-article-meta__value {
			text-align: left;
			color: #212529;
			font-weight: 400;
            display: flex;
            align-items: center;
		}

		.admin-blog-rubrics-section {
			margin-top: 0.5rem;
		}

		.admin-blog-rubrics-section .admin-dashboard__section-title {
			margin-top: 2rem;
		}

		.admin-blog-topic-list {
			display: grid;
			grid-template-columns: 1fr 1fr 1fr;
			gap: 0.5rem;
			max-width: 1200px;
			margin: 0 auto;
		}

		.admin-blog-topic-option {
			display: flex;
			border: 1px solid #dee2e6;
			border-radius: 0.375rem;
			padding: 10px 15px;
			line-height: 1.3;
			text-align: center;
            align-items: center;
            justify-content: center;
			cursor: pointer;
			transition: background-color 0.15s ease, border-color 0.15s ease;
			user-select: none;
		}

		.admin-blog-topic-option.is-selected {
			border-color: var(--bs-success);
			background-color: var(--bs-success-bg-subtle);
		}
	</style>

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
							<div class="col-12">
								<div class="admin-blog-article-meta">
									<span class="admin-blog-article-meta__label">ID:</span>
									<span class="admin-blog-article-meta__value"><?= $articleId ?></span>
									<span class="admin-blog-article-meta__label">Создан:</span>
									<span class="admin-blog-article-meta__value"><?= htmlspecialchars($formatAdminDatetime((string) ($article->created_at ?? ''))) ?></span>
									<span class="admin-blog-article-meta__label">Изменен:</span>
									<span class="admin-blog-article-meta__value"><?= htmlspecialchars($formatAdminDatetime((string) ($article->updated_at ?? ''))) ?></span>
									<span class="admin-blog-article-meta__label">Кол-во просмотров:</span>
									<span class="admin-blog-article-meta__value"><?= (int) ($article->views_count ?? 0) ?></span>
									<span class="admin-blog-article-meta__label">Кол-во комментариев:</span>
									<span class="admin-blog-article-meta__value"><?= $commentsCount ?></span>
									<span class="admin-blog-article-meta__label">Активность:</span>
									<span class="admin-blog-article-meta__value">
										<input class="form-check-input mt-0" type="checkbox" id="enabled" name="enabled" <?= $isEnabled ? 'checked' : '' ?>>
									</span>
								</div>
							</div>
						<?php endif; ?>

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

						<?php if ($isCreate): ?>
							<div class="col-12">
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" id="enabled" name="enabled" <?= $isEnabled ? 'checked' : '' ?>>
									<label class="form-check-label" for="enabled">Активная статья</label>
								</div>
							</div>
						<?php endif; ?>

						<div class="col-12 admin-blog-rubrics-section">
							<h3 class="admin-dashboard__section-title">
								<span class="admin-dashboard__section-title-line" aria-hidden="true"></span>
								<span class="admin-dashboard__section-title-text">Рубрики</span>
								<span class="admin-dashboard__section-title-line" aria-hidden="true"></span>
							</h3>
							<?php if (empty($topics)): ?>
								<div class="alert alert-warning mb-0">Рубрики не найдены.</div>
							<?php else: ?>
								<div class="admin-blog-topic-list">
									<?php foreach ($topics as $topic): ?>
										<?php
										$currentTopicId = (int) ($topic->id ?? 0);
										$currentTopicTitle = (string) ($topic->title ?? 'Без названия');
										$isChecked = in_array($currentTopicId, $selectedTopicIds, true);
										?>
										<label class="admin-blog-topic-option<?= $isChecked ? ' is-selected' : '' ?>">
											<input class="visually-hidden" type="checkbox" name="topic_ids[]" value="<?= $currentTopicId ?>" <?= $isChecked ? 'checked' : '' ?>>
											<span><?= htmlspecialchars($currentTopicTitle) ?></span>
										</label>
									<?php endforeach; ?>
								</div>
								<div class="form-text text-center mt-2">Первая выбранная рубрика будет сохранена как основная.</div>
							<?php endif; ?>
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
							<?php
							(new ContentEditor())->render([
								'id' => 'admin-article-editor',
								'name' => 'detail_text',
								'html' => $detailText,
								'class' => 'content-editor_admin',
								'uploadUrl' => '/admin/content-editor/upload/image/',
								'uploadFileUrl' => '/admin/content-editor/upload/file/',
								'extraUploadFields' => [
									'scope' => 'article',
									'entity_id' => (string) $articleId,
								],
							]);
							?>
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

	document.querySelectorAll('.admin-blog-topic-option input[type="checkbox"]').forEach((checkbox) => {
		checkbox.addEventListener('change', () => {
			const option = checkbox.closest('.admin-blog-topic-option');
			if (!option) {
				return;
			}

			option.classList.toggle('is-selected', checkbox.checked);
		});
	});

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
