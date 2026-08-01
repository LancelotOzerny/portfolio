<section class="light-page-hero">
	<div class="site-container light-page-hero__container">
		<h1 class="page-title"><?= \Modules\Main\Template::getInstance()->getParam('title') ?></h1>
		<p class="light-page-hero__text">
			Подборка работ, где я соединяю интерфейсы, backend-логику и аккуратную структуру проекта.
		</p>
	</div>
</section>

<section class="light-page-section">
	<div class="site-container">
		<?php
		(new \Components\ProjectsGrid\ProjectsGrid([
			'use_filters' => false,
			'show_tags' => false,
			'template' => 'Light',
		]))->render();
		?>
	</div>
</section>
