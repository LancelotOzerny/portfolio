<?php
$items = $this->getParam('items') ?? [];
$isAdmin = (bool) ($this->getParam('is_admin') ?? false);
$error = trim((string) ($this->getParam('error') ?? ''));
$defaultImage = '/Templates/Inner/img/no-image.webp';
?>

<?php if ($error !== ''): ?>
	<div class="alert alert-warning">
		Темы блога пока не удалось загрузить. Проверьте, что таблицы блога созданы.
	</div>
<?php endif; ?>

<div class="blog-topics">
	<?php if ($isAdmin): ?>
		<a class="blog-topic-card blog-topic-card_add" href="/admin/content/blog/rubrics/create/">
			<span class="blog-add-card__plus" aria-hidden="true">+</span>
			<span class="blog-add-card__text">Добавить тему</span>
		</a>
	<?php endif; ?>

	<?php foreach ($items as $topic): ?>
		<?php
		$topicId = (int) ($topic->id ?? 0);
		$title = (string) ($topic->title ?? 'Без названия');
		$previewText = (string) ($topic->preview_text ?? '');
		$imagePath = trim((string) ($topic->image_path ?? ''));
		$articlesCount = (int) ($topic->articles_count ?? 0);
		?>
		<a class="blog-topic-card" href="/blog/<?= $topicId ?>/">
			<img src="<?= htmlspecialchars($imagePath !== '' ? $imagePath : $defaultImage) ?>" alt="<?= htmlspecialchars($title) ?>">
			<span class="blog-topic-card__overlay" aria-hidden="true"></span>
			<span class="blog-topic-card__content">
				<span class="blog-topic-card__count"><?= $articlesCount ?> статьи</span>
				<span class="blog-topic-card__title"><?= htmlspecialchars($title) ?></span>
				<span class="blog-topic-card__text"><?= htmlspecialchars($previewText) ?></span>
			</span>
		</a>
	<?php endforeach; ?>
</div>
