<?php
/* @var array $data */

$topic = $data['topic'] ?? null;
$isAdmin = (bool) ($data['is_admin'] ?? false);
$renderRating = static function (int $rating): string {
	$rating = max(0, min(10, $rating));

	return str_repeat('★', $rating) . str_repeat('☆', 10 - $rating);
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
		<div class="blog-articles">
			<?php if ($isAdmin): ?>
				<button class="blog-article-card blog-article-card_add" type="button" onclick="alert('Статья добавлена')">
					<span class="blog-add-card__plus" aria-hidden="true">+</span>
					<span class="blog-add-card__text">Добавить статью</span>
				</button>
			<?php endif; ?>

			<?php foreach ($topic['articles'] as $article): ?>
				<a class="blog-article-card" href="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/">
					<span class="blog-article-card__image">
						<img src="<?= htmlspecialchars((string) $article['image']) ?>" alt="<?= htmlspecialchars((string) $article['title']) ?>">
					</span>
					<span class="blog-article-card__content">
						<span class="blog-article-card__date"><?= htmlspecialchars((string) $article['date']) ?></span>
						<span class="blog-article-card__title"><?= htmlspecialchars((string) $article['title']) ?></span>
						<span class="blog-article-card__text"><?= htmlspecialchars((string) $article['preview']) ?></span>
						<span class="blog-rating" aria-label="Оценка <?= (int) $article['rating'] ?> из 10">
							<span class="blog-rating__stars" aria-hidden="true"><?= $renderRating((int) $article['rating']) ?></span>
							<span class="blog-rating__value"><?= (int) $article['rating'] ?>/10</span>
						</span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
