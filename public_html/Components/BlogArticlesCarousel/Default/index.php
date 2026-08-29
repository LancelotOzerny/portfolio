<?php
$items = $this->getParam('items') ?? [];
$title = (string) $this->getParam('title');
$error = trim((string) ($this->getParam('error') ?? ''));
?>

<section class="blog-carousel-section" aria-labelledby="blogCarouselTitle">
	<div class="site-container blog-carousel-section__container">
		<div class="section-header">
			<h2 class="page-title" id="blogCarouselTitle"><?= htmlspecialchars($title) ?></h2>
			<a class="button button_secondary section-header__action" href="/blog/">Все статьи</a>
		</div>

		<?php if ($error !== ''): ?>
			<div class="blog-carousel-section__empty">Не удалось загрузить статьи блога.</div>
		<?php elseif ($items === []): ?>
			<div class="blog-carousel-section__empty">Пока нет опубликованных статей.</div>
		<?php else: ?>
			<div class="blog-carousel" data-blog-carousel>
				<button class="blog-carousel__nav blog-carousel__nav_prev" type="button" aria-label="Предыдущие статьи">
					<span aria-hidden="true">&larr;</span>
				</button>

				<div class="blog-carousel__viewport">
					<div class="blog-carousel__track">
						<?php foreach ($items as $item): ?>
							<a class="blog-carousel-card" href="<?= htmlspecialchars((string) $item['url']) ?>">
								<span class="blog-carousel-card__media">
									<img src="<?= htmlspecialchars((string) $item['image']) ?>" alt="<?= htmlspecialchars((string) $item['title']) ?>" loading="lazy" decoding="async">
								</span>
								<span class="blog-carousel-card__body">
									<?php if ($item['topic_title'] !== ''): ?>
										<span class="blog-carousel-card__topic"><?= htmlspecialchars((string) $item['topic_title']) ?></span>
									<?php endif; ?>
									<span class="blog-carousel-card__date"><?= htmlspecialchars((string) $item['date']) ?></span>
									<span class="blog-carousel-card__title"><?= htmlspecialchars((string) $item['title']) ?></span>
									<?php if ($item['preview'] !== ''): ?>
										<span class="blog-carousel-card__text"><?= htmlspecialchars((string) $item['preview']) ?></span>
									<?php endif; ?>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

				<button class="blog-carousel__nav blog-carousel__nav_next" type="button" aria-label="Следующие статьи">
					<span aria-hidden="true">&rarr;</span>
				</button>
			</div>
		<?php endif; ?>
	</div>
</section>
