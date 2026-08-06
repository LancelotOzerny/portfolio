<?php
/* @var array $data */

use App\Services\Blog\BlogDateFormatter;

$articles = $data['articles'] ?? [];
$error = trim((string) ($data['error'] ?? ''));
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
$defaultImage = '/Templates/Inner/img/no-image.webp';
$dateFormatter = new BlogDateFormatter();
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
	</style>

	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<h1 class="h3 mb-1">Статьи блога</h1>
			<p class="text-secondary mb-0">Список материалов блога.</p>
		</div>
		<div class="d-flex gap-2">
			<a class="btn btn-primary" href="/admin/content/blog/articles/create/">Создать статью</a>
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
									</div>

									<form action="/admin/content/blog/articles/<?= $articleId ?>/delete/" method="post" class="mt-auto mb-0" onsubmit="return confirm('Удалить статью «<?= htmlspecialchars($title, ENT_QUOTES) ?>»?');">
										<button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
									</form>
								</div>
							</div>
						</div>
					</article>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
