<section class="light-page-hero about-page-hero">
	<div class="site-container about-page-hero__container">
		<div>
			<h1 class="page-title page-title--left">О себе</h1>
			<p class="light-page-hero__text about-page-hero__text">
				Fullstack-разработчик, который любит понятные интерфейсы, аккуратную backend-логику и проекты, которые удобно развивать.
			</p>
		</div>

		<div class="about-page-hero__photo">
			<img src="/upload/images/main/profile.png" alt="Максим Беляков">
		</div>
	</div>
</section>

<section class="light-page-section about-page-approach" aria-labelledby="approachTitle">
	<div class="site-container about-page-approach__container">
		<h2 class="page-title" id="approachTitle">Подход к работе</h2>
		<div class="about-page-approach__text">
			<?php
			(new \Components\IncludeArea\IncludeArea([
				'path' => 'Light/about-approach.html',
			]))->render();
			?>
		</div>
	</div>
</section>

<?php
(new \Components\Resume\Resume([
	'show_experience' => true,
]))->render();
?>
