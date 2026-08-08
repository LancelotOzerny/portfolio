<?php
/* @var array $data */

$topic = $data['topic'] ?? null;
$isEditMode = (bool) ($data['edit_mode'] ?? false);

$renderStarsMarkup = static function (float $value, int $max = 5): string {
	$filled = (int) max(0, min($max, round($value)));
	$markup = '';

	for ($i = 1; $i <= $max; $i++) {
		$markup .= '<span class="blog-rating__star' . ($i <= $filled ? ' is-active' : '') . '" aria-hidden="true">★</span>';
	}

	return $markup;
};

if ($topic === null) {
	?>
	<section class="light-page-hero">
		<div class="site-container light-page-hero__container">
			<a class="project-detail__back" href="/blog/">Назад в блог</a>
			<h1 class="page-title">Тема не найдена</h1>
			<p class="light-page-hero__text">Возможно, ссылка изменилась или тестовая тема была удалена.</p>
		</div>
	</section>
	<?php
	return;
}

$detailImagePath = trim((string) ($topic['detail_image_path'] ?? ''));
?>

<?php if ($detailImagePath !== ''): ?>
	<section class="blog-topic-detail-image">
		<img src="<?= htmlspecialchars($detailImagePath) ?>" alt="<?= htmlspecialchars((string) $topic['name']) ?>">
	</section>
<?php endif; ?>

<section class="light-page-hero">
	<div class="site-container light-page-hero__container">
		<nav class="blog-breadcrumbs" aria-label="Хлебные крошки">
			<a href="/">Главная</a>
			<span>/</span>
			<a href="/blog/">Блог</a>
			<span>/</span>
			<span><?= htmlspecialchars((string) $topic['name']) ?></span>
		</nav>
		<h1 class="page-title"><?= htmlspecialchars((string) $topic['name']) ?></h1>
		<p class="light-page-hero__text"><?= htmlspecialchars((string) $topic['description']) ?></p>
	</div>
</section>

<section class="light-page-section blog-page">
	<div class="site-container">
		<div class="blog-articles blog-articles_cards">
			<?php if ($isEditMode): ?>
				<a class="blog-article-card blog-article-card_add" href="/admin/content/blog/articles/create/">
					<span class="blog-add-card__plus" aria-hidden="true">+</span>
					<span class="blog-add-card__text">Добавить статью</span>
				</a>
			<?php endif; ?>

			<?php if (empty($topic['articles'])): ?>
				<div class="blog-empty-state"><?= $isEditMode ? 'В этой рубрике пока нет статей.' : 'В этой рубрике пока нет активных статей.' ?></div>
			<?php endif; ?>

			<?php foreach ($topic['articles'] as $article): ?>
				<?php $isDisabled = array_key_exists('enabled', $article) && !$article['enabled']; ?>
				<a class="blog-article-card<?= $isDisabled ? ' blog-article-card_disabled' : '' ?>" href="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/">
					<span class="blog-article-card__image">
						<img src="<?= htmlspecialchars((string) $article['image']) ?>" alt="<?= htmlspecialchars((string) $article['title']) ?>">
					</span>
					<span class="blog-article-card__content">
						<?php if ($isEditMode && $isDisabled): ?>
							<span class="blog-article-card__status">Скрыта</span>
						<?php endif; ?>
						<span class="blog-article-card__meta">
							<span class="blog-article-card__date"><?= htmlspecialchars((string) $article['date']) ?></span>
							<?php if (array_key_exists('views_count', $article)): ?>
								<span class="blog-article-card__views" title="Просмотры">
									<svg class="blog-article-card__views-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
										<path d="M12 5C7 5 2.7 8.1 1 12c1.7 3.9 6 7 11 7s9.3-3.1 11-7c-1.7-3.9-6-7-11-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-6.5A2.5 2.5 0 1 0 12 14a2.5 2.5 0 0 0 0-5z"/>
									</svg>
									<span><?= (int) $article['views_count'] ?></span>
								</span>
							<?php endif; ?>
						</span>
						<span class="blog-article-card__title"><?= htmlspecialchars((string) $article['title']) ?></span>
						<span class="blog-article-card__text"><?= htmlspecialchars((string) $article['preview']) ?></span>
						<?php if (array_key_exists('rating', $article)): ?>
							<?php $cardRating = (float) ($article['rating'] ?? 0); ?>
							<span class="blog-rating blog-article-card__rating">
								<span class="blog-rating__stars"><?= $renderStarsMarkup($cardRating) ?></span>
								<span class="blog-rating__value"><?= number_format($cardRating, 1, '.', '') ?></span>
							</span>
						<?php endif; ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
