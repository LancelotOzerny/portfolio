<?php
$items = $this->getParam('items') ?? [];
$error = trim((string) ($this->getParam('error') ?? ''));
$flash = $this->getParam('flash');
$defaultImage = '/Templates/Inner/img/no-image.webp';
?>

<section class="admin-blog-sections">
	<style>
		.admin-blog-section-card {
			transition: transform 0.18s ease, box-shadow 0.18s ease;
		}

		.admin-blog-section-card:hover {
			transform: translateY(-3px);
			box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12) !important;
		}
	</style>

	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<h1 class="h3 mb-1">Рубрики блога</h1>
			<p class="text-secondary mb-0">Темы статей блога.</p>
		</div>
		<div class="d-flex gap-2">
			<a class="btn btn-primary" href="/admin/content/blog/rubrics/create/">
				Создать рубрику
			</a>
			<a href="/admin/" class="btn btn-outline-secondary">Назад в админку</a>
		</div>
	</div>

	<?php if (is_array($flash)): ?>
		<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>">
			<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
		</div>
	<?php endif; ?>

	<?php if ($error !== ''): ?>
		<div class="alert alert-danger">Не удалось загрузить темы блога. Проверьте наличие таблиц.</div>
	<?php endif; ?>

	<?php if (empty($items)): ?>
		<div class="alert alert-warning mb-0">Темы блога не найдены.</div>
	<?php else: ?>
		<div class="row g-3">
			<?php foreach ($items as $topic): ?>
				<?php
				$topicId = (int) ($topic->id ?? 0);
				$title = (string) ($topic->title ?? 'Без названия');
				$previewText = trim((string) ($topic->preview_text ?? ''));
				$shortPreviewText = strlen($previewText) > 200 ? substr($previewText, 0, 200) . '...' : $previewText;
				$imagePath = trim((string) ($topic->image_path ?? ''));
				$articlesCount = (int) ($topic->articles_count ?? 0);
				$isEnabled = (int) ($topic->enabled ?? 1) === 1;
				?>
				<div class="col-12 col-md-6 col-xl-4">
					<article class="card border-0 shadow-sm h-100 admin-blog-section-card">
						<img
							src="<?= htmlspecialchars($imagePath !== '' ? $imagePath : $defaultImage) ?>"
							class="card-img-top"
							style="object-fit: cover; height: 220px;"
							alt="<?= htmlspecialchars($title) ?>"
						>
						<div class="card-body d-flex flex-column">
							<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
								<h2 class="h5 mb-0">
									<a class="text-decoration-none text-reset" href="/admin/content/blog/rubrics/<?= $topicId ?>/">[<?= $topicId ?>] <?= htmlspecialchars($title) ?></a>
								</h2>
								<span class="badge <?= $isEnabled ? 'text-bg-success' : 'text-bg-secondary' ?>">
									<?= $isEnabled ? 'Активна' : 'Скрыта' ?>
								</span>
							</div>
							<p class="text-secondary flex-grow-1 mb-3">
								<?= htmlspecialchars($shortPreviewText !== '' ? $shortPreviewText : 'Preview текст не заполнен') ?>
							</p>
							<div class="small text-secondary">
								<div>Статей: <?= $articlesCount ?></div>
								<div>Создана: <?= htmlspecialchars((string) ($topic->created_at ?? '-')) ?></div>
								<div>Изменена: <?= htmlspecialchars((string) ($topic->updated_at ?? '-')) ?></div>
							</div>
							<form action="/admin/content/blog/rubrics/<?= $topicId ?>/delete/" method="post" class="mt-3 mb-0" onsubmit="return confirm('Удалить рубрику «<?= htmlspecialchars($title, ENT_QUOTES) ?>»?');">
								<button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
							</form>
						</div>
					</article>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
