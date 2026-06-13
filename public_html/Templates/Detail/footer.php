<footer class="site-footer scroll-show-area">
	<div class="container site-footer__container">
		<div class="site-footer__brand">
			<a class="site-footer__logo" href="/">LANCY</a>
			<p class="site-footer__description">
				Создаю современные веб-решения для вашего бизнеса с использованием PHP, MySQL, HTML, Bootstrap и jQuery.
			</p>
		</div>

		<nav class="site-footer__nav" aria-label="Sitemap">
			<?php
			(new \Components\Navigation\Navigation([
				'type' => 'Main',
				'template' => 'Column'
			]))->render();
			?>
		</nav>

		<div class="site-footer__contacts">
			<div class="site-footer__title">Контакты</div>
			<div class="site-footer__contact-text">Липецк, Россия</div>
			<a class="site-footer__contact-link" href="tel:+79205201831">8 (920) 520 18 31</a>
			<a class="site-footer__contact-link" href="mailto:lancelot.ozernuy@gmail.com">lancelot.ozernuy@gmail.com</a>
		</div>
	</div>

	<div class="site-footer__copyright">
		&copy; 2026 LANCY. Все права защищены.
	</div>
</footer>

<script src="/Templates/Default/js/bootstrap.bundle.min.js"></script>
<script src="/Templates/Inner/script.js"></script>
</body>
</html>
