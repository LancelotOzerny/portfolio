<?php
/* @var array $data */

$topic = $data['topic'] ?? null;
$article = $data['article'] ?? null;
$renderRating = static function (int $rating): string {
	$rating = max(0, min(10, $rating));

	return str_repeat('★', $rating) . str_repeat('☆', 10 - $rating);
};

if ($topic === null || $article === null) {
	?>
	<section class="light-page-hero">
		<div class="site-container light-page-hero__container">
			<a class="project-detail__back" href="/blog/">Назад в блог</a>
			<h1 class="page-title">Статья не найдена</h1>
			<p class="light-page-hero__text">Возможно, ссылка изменилась или тестовая статья была удалена.</p>
		</div>
	</section>
	<?php
	return;
}
?>

<section class="blog-detail-hero">
	<img src="<?= htmlspecialchars((string) $article['image']) ?>" alt="<?= htmlspecialchars((string) $article['title']) ?>">
	<div class="blog-detail-hero__overlay" aria-hidden="true"></div>
	<div class="site-container blog-detail-hero__container">
		<nav class="blog-breadcrumbs" aria-label="Хлебные крошки">
			<a href="/">Главная</a>
			<span>/</span>
			<a href="/blog/">Блог</a>
			<span>/</span>
			<a href="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/"><?= htmlspecialchars((string) $topic['name']) ?></a>
			<span>/</span>
			<span><?= htmlspecialchars((string) $article['title']) ?></span>
		</nav>
		<h2><?= htmlspecialchars((string) $article['title']) ?></h2>
		<p><?= htmlspecialchars((string) $article['preview']) ?></p>
		<div class="blog-detail__meta">
			<span><?= htmlspecialchars((string) $article['date']) ?></span>
			<span class="blog-rating" aria-label="Оценка <?= (int) $article['rating'] ?> из 10">
				<span class="blog-rating__stars" aria-hidden="true"><?= $renderRating((int) $article['rating']) ?></span>
				<span class="blog-rating__value"><?= (int) $article['rating'] ?>/10</span>
			</span>
		</div>
	</div>
</section>

<section class="light-page-section blog-page blog-detail">
	<div class="site-container">
		<article class="blog-detail__content">
			<?php foreach ($article['content'] as $paragraph): ?>
				<p><?= htmlspecialchars((string) $paragraph) ?></p>
			<?php endforeach; ?>
		</article>

		<form class="blog-comment-form" action="#" method="post">
			<h3>Добавить комментарий</h3>
			<label>
				<span>Имя</span>
				<input type="text" name="name" placeholder="Ваше имя">
			</label>
			<label>
				<span>Комментарий</span>
				<textarea name="comment" rows="5" placeholder="Ваш комментарий"></textarea>
			</label>
			<button class="button button_dark" type="submit">Отправить</button>
		</form>

		<section class="blog-comments" aria-labelledby="blogCommentsTitle">
			<h2 id="blogCommentsTitle">Комментарии</h2>

			<?php foreach ($article['comments'] as $comment): ?>
				<div class="blog-comment">
					<strong><?= htmlspecialchars((string) $comment['author']) ?></strong>
					<p><?= htmlspecialchars((string) $comment['text']) ?></p>
				</div>
			<?php endforeach; ?>
		</section>
	</div>
</section>
