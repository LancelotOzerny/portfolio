<?php
/** @var \Modules\Main\Template $this */
?>
</main>

<?php if ($this->getParam('show_contact_cta') !== false): ?>
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
<?php endif; ?>

<footer class="light-footer">
	<div class="site-container light-footer__container">
		<div class="light-footer__top">
			<a class="light-footer__brand" href="/">LANCY</a>
			<a class="light-footer__button" href="/contacts/">Связаться</a>
		</div>

		<nav class="light-footer__sitemap" aria-label="Карта сайта">
			<a href="/">Главная</a>
			<a href="/about/">О себе</a>
			<a href="/projects/">Проекты</a>
			<a href="/contacts/">Контакты</a>
		</nav>

		<div class="light-footer__bottom">
			<span>&copy; 2026 Максим Беляков. Все права защищены.</span>
		</div>
	</div>
</footer>
</body>
</html>
