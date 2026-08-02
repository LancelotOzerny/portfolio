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
		<?php
		(new \Components\BlogSections\BlogSections([
			'template' => 'Default',
			'is_admin' => (bool) ($data['is_admin'] ?? false),
			'edit_mode' => (bool) ($data['edit_mode'] ?? false),
			'only_enabled' => true,
		]))->render();
		?>
	</div>
</section>
