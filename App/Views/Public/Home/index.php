<section class="hero-section">
	<div class="site-container hero-section__container">
		<div class="hero-section__content">
			<h1 class="page-title page-title--left" style="margin-bottom: 25px;">
				<?php
				(new \Components\IncludeArea\IncludeArea([
					'path' => 'Light/hero-title.html',
				]))->render();
				?>
			</h1>
			<div class="about-light__text">
				<?php
				(new \Components\IncludeArea\IncludeArea([
					'path' => 'Light/hero-description.html',
				]))->render();
				?>
			</div>
			<div class="hero-section__actions" aria-label="Основные ссылки">
				<a class="button button_primary" href="/projects/">Проекты</a>
				<a class="button button_secondary" href="https://github.com/" target="_blank" rel="noopener">GitHub</a>
			</div>
		</div>

		<div class="hero-photo" aria-label="Фотография Максима Белякова">
            <?php
            (new \Components\ImagePreview\ImagePreview([
                'edit_key' => 'home.hero-photo',
                'path' => '/upload/images/main/profile.png',
                'alt' => 'Максим Беляков',
            ]))->render();
            ?>
		</div>
	</div>
</section>

<section class="social-section" aria-labelledby="socialTitle">
	<div class="site-container">
		<h2 class="section-kicker" id="socialTitle">Соц сети</h2>
		<?php (new \Components\SocialNetworks\SocialNetworks())->render(); ?>
	</div>
</section>

<section class="projects-light" aria-labelledby="projectsTitle">
	<div class="site-container projects-light__container">
		<h2 class="page-title" id="projectsTitle">Проекты</h2>
		<?php
		(new \Components\ProjectsGrid\ProjectsGrid([
			'edit_key' => 'home.projects-grid',
			'use_filters' => false,
			'show_tags' => false,
			'limit' => 6,
			'template' => 'Light',
		]))->render();
		?>
	</div>
</section>

<section class="about-light" aria-labelledby="aboutTitle">
	<div class="site-container about-light__container">
		<h2 class="page-title" id="aboutTitle">Немного о себе</h2>
		<div class="about-light__grid">
			<div class="about-light__photo">
				<img src="/upload/images/main/profile.png" alt="Максим Беляков">
			</div>
			<div class="about-light__text">
				<?php
				(new \Components\IncludeArea\IncludeArea([
					'path' => 'Light/about.html',
				]))->render();
				?>
			</div>
		</div>
	</div>
</section>
