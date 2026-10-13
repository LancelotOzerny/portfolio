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
			<div class="hero-section__text">
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
				'loading' => 'eager',
            ]))->render();
            ?>
		</div>
	</div>
</section>

<?php (new \Components\SocialNetworks\SocialNetworks([
        'template' => 'BlackLine'
]))->render(); ?>

<section class="home-about" aria-labelledby="aboutTitle" data-home-about>
	<div class="site-container home-about__container">
		<div class="section-header">
			<h2 class="page-title" id="aboutTitle">Обо мне</h2>
		</div>
		<div class="home-about__top">
			<div class="home-about__intro">
				<h3 class="home-about__intro-title">
					Я web-разработчик, который обожает превращать <span>безумные идеи в 
					работающие решения</span>
				</h3>
				<?php
				(new \Components\IncludeArea\IncludeArea([
					'path' => 'Light/home-about/intro.html',
				]))->render();
				?>
				<div class="home-about__actions">
					<a class="home-about__primary" href="/about/">Больше обо мне <span aria-hidden="true">→</span></a>
					<a class="home-about__secondary" href="/upload/docs/resume.pdf" download="Maxim-Belyakov-Resume.pdf">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M12 12v7m-3-3 3 3 3-3"/></svg>
						<span>Скачать резюме <small>PDF</small></span>
					</a>
				</div>
			</div>

			<div class="home-about__expertise">
				<div class="home-about__counter" aria-live="polite">
					<strong data-home-about-counter>01</strong>&nbsp;/&nbsp;04
				</div>
				<div class="home-about__accordion">
					<article class="home-about__item is-open">
						<h3>
							<button class="home-about__toggle" type="button" id="home-about-tab-backend" aria-expanded="true" aria-controls="home-about-panel-backend">
								<span class="home-about__number">01</span>
								<span class="home-about__label">Backend-разработка</span>
								<svg class="home-about__chevron" viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true">
									<path d="m5 15 7-7 7 7"/>
								</svg>
							</button>
						</h3>
						<div class="home-about__panel is-visible" id="home-about-panel-backend" role="region" aria-labelledby="home-about-tab-backend">
							<div class="home-about__panel-inner">
								<?php
								(new \Components\IncludeArea\IncludeArea([
									'path' => 'Light/home-about/backend.html',
								]))->render();
								?>
								<div class="home-about__tags">
									<span>PHP</span>
									<span>1С-Битрикс</span>
									<span>MySQL</span>
									<span>Cron</span>
								</div>
							</div>
						</div>
					</article>
					<article class="home-about__item">
						<h3>
							<button class="home-about__toggle" type="button" id="home-about-tab-frontend" aria-expanded="false" aria-controls="home-about-panel-frontend">
								<span class="home-about__number">02</span>
								<span class="home-about__label">Frontend-разработка</span>
								<svg class="home-about__chevron" viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true">
									<path d="m5 15 7-7 7 7"/>
								</svg>
							</button>
						</h3>
						<div class="home-about__panel" id="home-about-panel-frontend" role="region" aria-labelledby="home-about-tab-frontend" hidden>
							<div class="home-about__panel-inner">
								<?php
								(new \Components\IncludeArea\IncludeArea([
									'path' => 'Light/home-about/frontend.html',
								]))->render();
								?>
								<div class="home-about__tags">
									<span>HTML</span>
									<span>CSS</span>
									<span>JavaScript</span>
								</div>
							</div>
						</div>
					</article>
					<article class="home-about__item">
						<h3>
							<button class="home-about__toggle" type="button" id="home-about-tab-api" aria-expanded="false" aria-controls="home-about-panel-api">
								<span class="home-about__number">03</span>
								<span class="home-about__label">Интеграции и API</span>
								<svg class="home-about__chevron" viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true">
									<path d="m5 15 7-7 7 7"/>
								</svg>
							</button>
						</h3>
						<div class="home-about__panel" id="home-about-panel-api" role="region" aria-labelledby="home-about-tab-api" hidden>
							<div class="home-about__panel-inner">
								<?php
								(new \Components\IncludeArea\IncludeArea([
									'path' => 'Light/home-about/api.html',
								]))->render();
								?>
								<div class="home-about__tags">
									<span>REST API</span>
									<span>JSON</span>
									<span>Интеграции</span>
									<span>Мониторинг</span>
								</div>
							</div>
						</div>
					</article>
					<article class="home-about__item">
						<h3>
							<button class="home-about__toggle" type="button" id="home-about-tab-support" aria-expanded="false" aria-controls="home-about-panel-support">
								<span class="home-about__number">04</span>
								<span class="home-about__label">Поддержка и оптимизация</span>
								<svg class="home-about__chevron" viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true">
									<path d="m5 15 7-7 7 7"/>
								</svg>
							</button>
						</h3>
						<div class="home-about__panel" id="home-about-panel-support" role="region" aria-labelledby="home-about-tab-support" hidden>
							<div class="home-about__panel-inner">
								<?php
								(new \Components\IncludeArea\IncludeArea([
									'path' => 'Light/home-about/support.html',
								]))->render();
								?>
								<div class="home-about__tags">
									<span>Отладка</span>
									<span>Логирование</span>
									<span>Оптимизация</span>
									<span>Git</span>
								</div>
							</div>
						</div>
					</article>
				</div>
			</div>
		</div>

		<div class="home-about__bottom">
			<div class="home-about__outside">
				<h3>За пределами работы</h3>
				<?php
				(new \Components\IncludeArea\IncludeArea([
					'path' => 'Light/home-about/outside.html',
				]))->render();
				?>
			</div>
			<div class="home-about__hobbies">
				<div class="home-about__hobby">
					<div class="home-about__hobby-icon">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<path d="M7 9h10c2 0 3.5 1.3 4 4l1 4c.5 3-2 4-4 2l-3-3H9l-3 3c-2 2-4.5 1-4-2l1-4c.5-2.7 2-4 4-4Z"/>
							<path d="M7 12v5M4.5 14.5h5M16 12h.01M19 14h.01"/>
						</svg>
					</div>
					<h4>GameDev</h4>
					<?php
					(new \Components\IncludeArea\IncludeArea([
						'path' => 'Light/home-about/gamedev.html',
					]))->render();
					?>
				</div>
				<div class="home-about__hobby">
					<div class="home-about__hobby-icon">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<rect x="4" y="7" width="16" height="13" rx="3"/>
							<path d="M12 3v4M9 3h6M8 12h.01M16 12h.01M9 16c2 1 4 1 6 0M2 10h2M20 10h2"/>
						</svg>
					</div>
					<h4>Робототехника</h4>
					<?php
					(new \Components\IncludeArea\IncludeArea([
						'path' => 'Light/home-about/robotics.html',
					]))->render();
					?>
				</div>
				<div class="home-about__hobby">
					<div class="home-about__hobby-icon">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<rect x="6" y="6" width="12" height="12" rx="2"/>
							<path d="M9 9h6v6H9zM9 2v4M15 2v4M9 18v4M15 18v4M2 9h4M2 15h4M18 9h4M18 15h4"/>
						</svg>
					</div>
					<h4>Эксперименты</h4>
					<?php
					(new \Components\IncludeArea\IncludeArea([
						'path' => 'Light/home-about/experiments.html',
					]))->render();
					?>
				</div>
				<div class="home-about__hobby">
					<div class="home-about__hobby-icon">
						<svg viewBox="0 0 24 24" aria-hidden="true">
							<path d="M12 3a7 7 0 0 0-4 12.7c.7.5 1 1.1 1 2V19h6v-1.3c0-.9.3-1.5 1-2A7 7 0 0 0 12 3ZM9 22h6"/>
						</svg>
					</div>
					<h4>Блог</h4>
					<?php
					(new \Components\IncludeArea\IncludeArea([
						'path' => 'Light/home-about/blog.html',
					]))->render();
					?>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
(() => {
	const root = document.querySelector('[data-home-about]');
	if (!root) return;

	const buttons = Array.from(root.querySelectorAll('.home-about__toggle'));
	const counter = root.querySelector('[data-home-about-counter]');
	const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

	const setOpen = (activeIndex) => {
		buttons.forEach((button, index) => {
			const isOpen = index === activeIndex;
			const panel = root.querySelector('#' + button.getAttribute('aria-controls'));
			button.setAttribute('aria-expanded', String(isOpen));
			button.closest('.home-about__item').classList.toggle('is-open', isOpen);

			if (isOpen) {
				panel.hidden = false;
				if (!reducedMotion) requestAnimationFrame(() => panel.classList.add('is-visible'));
				else panel.classList.add('is-visible');
			} else {
				panel.classList.remove('is-visible');
				window.setTimeout(() => {
					if (button.getAttribute('aria-expanded') === 'false') panel.hidden = true;
				}, reducedMotion ? 0 : 250);
			}
		});

		counter.textContent = String(activeIndex + 1).padStart(2, '0');
	};

	buttons.forEach((button, index) => button.addEventListener('click', () => setOpen(index)));
	setOpen(0);
})();
</script>

<?php
(new \Components\BlogArticlesCarousel\BlogArticlesCarousel([
	'edit_key' => 'home.blog-carousel',
	'limit' => 6,
	'title' => 'Блог',
]))->render();
?>

<section class="projects-light" aria-labelledby="projectsTitle">
	<div class="site-container projects-light__container">
		<div class="section-header">
			<h2 class="page-title" id="projectsTitle">Проекты</h2>
			<a class="button button_secondary section-header__action" href="/projects/">Все проекты</a>
		</div>
		<?php
		(new \Components\ProjectsGrid\ProjectsGrid([
			'limit' => 7,
			'template' => 'Main',
		]))->render();
		?>
	</div>
</section>
