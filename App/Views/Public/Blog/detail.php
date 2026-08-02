<?php
/* @var array $data */

$topic = $data['topic'] ?? null;
$article = $data['article'] ?? null;

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
	</div>
</section>
