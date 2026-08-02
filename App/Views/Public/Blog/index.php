<?php
/* @var array $data */

$topics = $data['topics'] ?? [];
$isAdmin = (bool) ($data['is_admin'] ?? false);
?>

<section class="light-page-hero">
	<div class="site-container light-page-hero__container">
		<h1 class="page-title"><?= htmlspecialchars((string) \Modules\Main\Template::getInstance()->getParam('title')) ?></h1>
		<p class="light-page-hero__text">
			Тестовый раздел с темами будущих статей. Сейчас здесь только статическая верстка без базы данных.
		</p>
	</div>
</section>

<section class="light-page-section blog-page">
	<div class="site-container">
		<div class="blog-topics">
			<?php if ($isAdmin): ?>
				<button class="blog-topic-card blog-topic-card_add" type="button" onclick="alert('Add Theme')">
					<span class="blog-add-card__plus" aria-hidden="true">+</span>
					<span class="blog-add-card__text">Добавить тему</span>
				</button>
			<?php endif; ?>

			<?php foreach ($topics as $topic): ?>
				<a class="blog-topic-card" href="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/">
					<img src="<?= htmlspecialchars((string) $topic['image']) ?>" alt="<?= htmlspecialchars((string) $topic['name']) ?>">
					<span class="blog-topic-card__overlay" aria-hidden="true"></span>
					<span class="blog-topic-card__content">
						<span class="blog-topic-card__count"><?= count($topic['articles']) ?> статьи</span>
						<span class="blog-topic-card__title"><?= htmlspecialchars((string) $topic['name']) ?></span>
						<span class="blog-topic-card__text"><?= htmlspecialchars((string) $topic['description']) ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
