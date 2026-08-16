<?php
/* @var array $data */

use App\Services\Blog\BlogArticlePublicationService;
use App\Services\Blog\BlogDateFormatter;

$articles = $data['articles'] ?? [];
$error = trim((string) ($data['error'] ?? ''));
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
$defaultImage = '/Templates/Inner/img/no-image.webp';
$dateFormatter = new BlogDateFormatter();
$publicationService = new BlogArticlePublicationService();
?>

<section class="admin-blog-articles">
	<style>
		.admin-blog-article-card {
			transition: transform 0.18s ease, box-shadow 0.18s ease;
		}

		.admin-blog-article-card:hover {
			transform: translateY(-3px);
			box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12) !important;
		}

		.admin-blog-icon-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 38px;
			height: 38px;
			padding: 0;
			cursor: pointer;
		}

		.admin-blog-article-card__icon-btn {
			width: 31px;
			height: 31px;
		}
	</style>

	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<h1 class="h3 mb-1">Статьи блога</h1>
			<p class="text-secondary mb-0">Список материалов блога.</p>
		</div>
		<div class="d-flex gap-2">
			<form action="/admin/content/blog/articles/import/" method="post" enctype="multipart/form-data" class="mb-0">
				<input
					id="blog-article-import-file"
					class="d-none"
					type="file"
					name="article_file"
					accept="application/json,.json"
					onchange="this.form.submit()"
				>
				<label
					for="blog-article-import-file"
					class="btn btn-outline-secondary admin-blog-icon-btn mb-0"
					title="Импортировать статью"
					aria-label="Импортировать статью"
					role="button"
				>
					<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 21V9"/>
						<path d="M7 14l5-5 5 5"/>
						<path d="M5 3h14"/>
					</svg>
				</label>
			</form>
			<a
				class="btn btn-primary admin-blog-icon-btn"
				href="/admin/content/blog/articles/create/"
				title="Создать статью"
				aria-label="Создать статью"
			>
				<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
					<path d="M12 5v14M5 12h14"/>
				</svg>
			</a>
			<a href="/admin/" class="btn btn-outline-secondary">Назад в админку</a>
		</div>
	</div>

	<?php if ($flash !== null): ?>
		<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>">
			<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
		</div>
	<?php endif; ?>

	<?php if ($error !== ''): ?>
		<div class="alert alert-danger">Не удалось загрузить статьи: <?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if (empty($articles)): ?>
		<div class="alert alert-warning mb-0">Статьи блога не найдены.</div>
	<?php else: ?>
		<div class="row g-3">
			<?php foreach ($articles as $article): ?>
				<?php
				$articleId = (int) ($article->id ?? 0);
				$title = (string) ($article->title ?? 'Без названия');
				$topicTitle = trim((string) ($article->topic_title ?? ''));
				$previewText = trim((string) ($article->preview_text ?? ''));
				$shortPreviewText = strlen($previewText) > 220 ? substr($previewText, 0, 220) . '...' : $previewText;
				$previewImagePath = trim((string) ($article->preview_image_path ?? ''));
				$isEnabled = (int) ($article->enabled ?? 1) === 1;
				$isPublished = $publicationService->isPublished($article);
				$publicationDatetime = $publicationService->getPublicationDatetime($article);
				$scheduledDatetime = $publicationService->getScheduledDatetime($article);
				$scheduleInputValue = $publicationService->formatForInput(
					$scheduledDatetime !== null ? $scheduledDatetime : date('Y-m-d H:i:s', strtotime('+1 hour'))
				);
				?>
				<div class="col-12">
					<article class="card border-0 shadow-sm h-100 admin-blog-article-card">
						<div class="row g-0">
							<div class="col-12 col-md-3">
								<img
									src="<?= htmlspecialchars($previewImagePath !== '' ? $previewImagePath : $defaultImage) ?>"
									class="img-fluid rounded-start w-100"
									style="object-fit: cover; height: 220px; max-height: 300px;"
									alt="<?= htmlspecialchars($title) ?>"
								>
							</div>

							<div class="col-12 col-md-9">
								<div class="card-body h-100 d-flex flex-column">
									<div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
										<h2 class="h5 mb-1">
											<a class="text-decoration-none text-reset" href="/admin/content/blog/articles/<?= $articleId ?>/">[<?= $articleId ?>] <?= htmlspecialchars($title) ?></a>
										</h2>
										<span class="badge <?= $isEnabled ? 'text-bg-success' : 'text-bg-secondary' ?>">
											<?= $isEnabled ? 'Активна' : 'Скрыта' ?>
										</span>
									</div>

									<p class="text-secondary mb-3">
										<?= htmlspecialchars($shortPreviewText !== '' ? $shortPreviewText : 'Preview текст не заполнен') ?>
									</p>

									<div class="small text-secondary mb-3">
										<div>Рубрика: <?= htmlspecialchars($topicTitle !== '' ? $topicTitle : 'не указана') ?></div>
										<div>Автор: <?= htmlspecialchars((string) ($article->author ?? '-')) ?></div>
										<div class="d-flex align-items-center gap-1">
											<span>Просмотры:</span>
											<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor">
												<path d="M12 5C7 5 2.7 8.1 1 12c1.7 3.9 6 7 11 7s9.3-3.1 11-7c-1.7-3.9-6-7-11-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6.5A2.5 2.5 0 1 0 12 14a2.5 2.5 0 0 0 0-5z"/>
											</svg>
											<span><?= (int) ($article->views_count ?? 0) ?></span>
										</div>
										<div>Создана: <?= htmlspecialchars($dateFormatter->format((string) ($article->created_at ?? '')) ?: '-') ?></div>
										<div>Изменена: <?= htmlspecialchars($dateFormatter->format((string) ($article->updated_at ?? '')) ?: '-') ?></div>
										<div>Опубликована: <?= htmlspecialchars($publicationDatetime !== null ? ($dateFormatter->format($publicationDatetime) ?: $publicationDatetime) : 'Нет') ?></div>
										<?php if ($scheduledDatetime !== null): ?>
											<div>Запланирована: <?= htmlspecialchars($dateFormatter->format($scheduledDatetime) ?: $scheduledDatetime) ?></div>
										<?php endif; ?>
									</div>

									<div class="d-flex flex-wrap align-items-center gap-2 mt-auto mb-0">
										<?php if (!$isPublished): ?>
											<form action="/admin/content/blog/articles/<?= $articleId ?>/publish/" method="post" class="mb-0" onsubmit="return confirm('Опубликовать статью «<?= htmlspecialchars($title, ENT_QUOTES) ?>»?');">
												<input type="hidden" name="back" value="/admin/content/blog/articles/">
												<button type="submit" class="btn btn-success btn-sm">Опубликовать</button>
											</form>
											<span class="d-inline-flex align-items-center gap-2">
												<button
													type="button"
													class="btn btn-outline-primary btn-sm admin-blog-icon-btn admin-blog-article-card__icon-btn"
													title="Опубликовать потом"
													aria-label="Опубликовать потом статью «<?= htmlspecialchars($title, ENT_QUOTES) ?>»"
													data-bs-toggle="modal"
													data-bs-target="#blog-schedule-modal-<?= $articleId ?>"
												>
													<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
														<circle cx="12" cy="12" r="10"/>
														<path d="M12 6v6l4 2"/>
													</svg>
												</button>
												<?php if ($scheduledDatetime !== null): ?>
													<span class="small text-secondary"><?= htmlspecialchars($dateFormatter->formatWithTime($scheduledDatetime) ?: $scheduledDatetime) ?></span>
												<?php endif; ?>
											</span>
										<?php endif; ?>
										<form action="/admin/content/blog/articles/<?= $articleId ?>/delete/" method="post" class="mb-0" onsubmit="return confirm('Удалить статью «<?= htmlspecialchars($title, ENT_QUOTES) ?>»?');">
											<button
												type="submit"
												class="btn btn-outline-danger btn-sm admin-blog-icon-btn admin-blog-article-card__icon-btn"
												title="Удалить"
												aria-label="Удалить статью «<?= htmlspecialchars($title, ENT_QUOTES) ?>»"
											>
												<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
													<path d="M4 7h16"/>
													<path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
													<path d="M10 11v6"/>
													<path d="M14 11v6"/>
													<path d="M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/>
												</svg>
											</button>
										</form>
										<a
											href="/admin/content/blog/articles/<?= $articleId ?>/export/"
											class="btn btn-outline-secondary btn-sm admin-blog-icon-btn admin-blog-article-card__icon-btn"
											title="Экспортировать"
											aria-label="Экспортировать статью «<?= htmlspecialchars($title, ENT_QUOTES) ?>»"
										>
											<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
												<path d="M12 3v12"/>
												<path d="M7 10l5 5 5-5"/>
												<path d="M5 21h14"/>
											</svg>
										</a>
									</div>
								</div>
							</div>
						</div>
					</article>
				</div>
			<?php endforeach; ?>
		</div>

		<?php foreach ($articles as $article): ?>
			<?php
			if ($publicationService->isPublished($article)) {
				continue;
			}

			$articleId = (int) ($article->id ?? 0);
			$title = (string) ($article->title ?? 'Без названия');
			$scheduledDatetime = $publicationService->getScheduledDatetime($article);
			$scheduleInputValue = $publicationService->formatForInput(
				$scheduledDatetime !== null ? $scheduledDatetime : date('Y-m-d H:i:s', strtotime('+1 hour'))
			);
			?>
			<div class="modal fade" id="blog-schedule-modal-<?= $articleId ?>" tabindex="-1" aria-labelledby="blog-schedule-modal-label-<?= $articleId ?>" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<form action="/admin/content/blog/articles/<?= $articleId ?>/schedule/" method="post">
							<input type="hidden" name="back" value="/admin/content/blog/articles/">
							<div class="modal-header">
								<h2 class="modal-title h5" id="blog-schedule-modal-label-<?= $articleId ?>">Опубликовать потом</h2>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
							</div>
							<div class="modal-body">
								<p class="text-secondary"><?= htmlspecialchars($title) ?></p>
								<label class="form-label" for="blog-schedule-at-<?= $articleId ?>">Время публикации</label>
								<input
									type="datetime-local"
									class="form-control"
									id="blog-schedule-at-<?= $articleId ?>"
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
		<?php endforeach; ?>
	<?php endif; ?>
</section>
