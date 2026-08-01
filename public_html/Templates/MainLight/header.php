<?php
/** @var \Modules\Main\Template $this */
?>
<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php include \Modules\Main\App::getInstance()->root . '/public_html/Templates/Shared/seo.php'; ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="/Templates/MainLight/styles.css">
</head>
<body class="mainlight-page">
<?php
(new \Components\AdminBar\AdminBar())->render();
(new \Components\Navigation\Navigation([
	'type' => 'Main',
	'template' => 'College',
]))->render();
?>

<main class="mainlight">
	<section class="hero-section">
		<div class="site-container hero-section__container">
			<div class="hero-section__content">
				<h1 class="page-title  page-title--left" style="margin-bottom: 25px;">
					<?php
					(new \Components\IncludeArea\IncludeArea([
						'path' => 'MainLight/hero-title.html',
					]))->render();
					?>
				</h1>
				<div class="about-light__text">
					<?php
					(new \Components\IncludeArea\IncludeArea([
						'path' => 'MainLight/hero-description.html',
					]))->render();
					?>
				</div>
				<div class="hero-section__actions" aria-label="Основные ссылки">
					<a class="button button_primary" href="/portfolio/">Проекты</a>
					<a class="button button_secondary" href="https://github.com/" target="_blank" rel="noopener">GitHub</a>
				</div>
			</div>

			<div class="hero-photo" aria-label="Фотография Максима Белякова">
				<img src="/upload/images/main/profile.png" alt="Максим Беляков">
			</div>
		</div>
	</section>

	<section class="social-section" aria-labelledby="socialTitle">
		<div class="site-container">
			<h2 class="section-kicker" id="socialTitle">Соц сети</h2>
			<div class="social-grid">
				<a class="social-card" href="https://vk.com/" target="_blank" rel="noopener" aria-label="ВКонтакте">
					<span class="social-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false">
							<path d="M13.16 17.15c-5.07 0-7.96-3.48-8.08-9.27h2.54c.08 4.25 1.96 6.05 3.45 6.42V7.88h2.39v3.66c1.46-.16 3-1.83 3.52-3.66h2.39a7.05 7.05 0 0 1-3.25 4.62 7.31 7.31 0 0 1 3.81 4.65h-2.63c-.57-1.75-1.98-3.11-3.84-3.3v3.3h-.3Z"/>
						</svg>
					</span>
					<span class="social-card__name">ВКонтакте</span>
				</a>

				<a class="social-card" href="https://www.linkedin.com/" target="_blank" rel="noopener" aria-label="LinkedIn">
					<span class="social-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false">
							<path d="M6.94 8.86H4.1V19h2.84V8.86ZM5.52 4a1.64 1.64 0 1 0 0 3.28 1.64 1.64 0 0 0 0-3.28Zm13.9 9.45c0-3.04-1.62-4.46-3.79-4.46a3.25 3.25 0 0 0-2.93 1.61V8.86H9.98V19h2.83v-5.02c0-1.32.25-2.59 1.88-2.59 1.6 0 1.62 1.5 1.62 2.67V19h2.83l.28-5.55Z"/>
						</svg>
					</span>
					<span class="social-card__name">LinkedIn</span>
				</a>

				<a class="social-card" href="https://github.com/" target="_blank" rel="noopener" aria-label="GitHub">
					<span class="social-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false">
							<path d="M12 2.5a9.5 9.5 0 0 0-3 18.51c.47.09.64-.2.64-.45v-1.7c-2.61.57-3.16-1.11-3.16-1.11-.43-1.09-1.04-1.38-1.04-1.38-.86-.58.06-.57.06-.57.94.07 1.44.97 1.44.97.84 1.43 2.2 1.02 2.73.78.09-.61.33-1.02.6-1.25-2.08-.24-4.27-1.04-4.27-4.63 0-1.02.36-1.86.96-2.51-.1-.24-.42-1.2.09-2.48 0 0 .79-.25 2.58.96A8.9 8.9 0 0 1 12 6.82c.8 0 1.6.11 2.36.32 1.79-1.21 2.57-.96 2.57-.96.52 1.28.2 2.24.1 2.48.6.65.96 1.49.96 2.51 0 3.6-2.19 4.39-4.28 4.62.34.29.64.86.64 1.74v2.58c0 .25.17.55.65.45A9.5 9.5 0 0 0 12 2.5Z"/>
						</svg>
					</span>
					<span class="social-card__name">GitHub</span>
				</a>

				<a class="social-card" href="https://max.ru/" target="_blank" rel="noopener" aria-label="Max">
					<span class="social-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false">
							<path d="M4.2 18.9V5.1h3.1l4.7 7.34 4.72-7.34h3.08v13.8h-3.18V10.42l-3.78 5.74h-1.68l-3.78-5.74v8.48H4.2Z"/>
						</svg>
					</span>
					<span class="social-card__name">Max</span>
				</a>

				<a class="social-card" href="https://t.me/" target="_blank" rel="noopener" aria-label="Telegram">
					<span class="social-card__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false">
							<path d="M20.86 4.46 17.98 18c-.22.96-.8 1.2-1.62.75l-4.38-3.23-2.11 2.03c-.24.24-.43.43-.88.43l.31-4.46 8.12-7.34c.35-.31-.08-.48-.54-.17L6.83 12.34 2.5 10.99c-.94-.3-.96-.94.2-1.39l16.93-6.52c.78-.29 1.47.19 1.23 1.38Z"/>
						</svg>
					</span>
					<span class="social-card__name">Telegram</span>
				</a>
			</div>
		</div>
	</section>

	<section class="projects-light" aria-labelledby="projectsTitle">
		<div class="site-container projects-light__container">
			<h2 class="page-title" id="projectsTitle">Проекты</h2>
			<?php
			(new \Components\ProjectsGrid\ProjectsGrid([
				'use_filters' => false,
				'show_tags' => false,
				'limit' => 6,
				'template' => 'MainLight',
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
						'path' => 'MainLight/about.html',
					]))->render();
					?>
				</div>
			</div>
		</div>
	</section>

	<section class="contact-light" aria-labelledby="contactTitle">
		<div class="site-container contact-light__container contact-cta">
			<div class="contact-cta__content">
				<h2 class="contact-cta__title" id="contactTitle">Связаться со мной</h2>
				<p class="contact-cta__text">
					Расскажите о задаче, проекте или идее. Отвечу и подскажу, с чего лучше начать.
				</p>
			</div>
			<a class="button button_secondary contact-cta__button" href="/contacts/">Написать</a>
		</div>
	</section>
</main>

<div class="mainlight-hidden-content" hidden>
