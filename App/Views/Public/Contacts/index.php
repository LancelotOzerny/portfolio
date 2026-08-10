<section class="light-page-hero">
	<div class="site-container light-page-hero__container">
		<h1 class="page-title"><?= \Modules\Main\Template::getInstance()->getParam('title') ?></h1>
		<p class="light-page-hero__text">
			Если есть вопросы или предложения - я на связи!
		</p>
	</div>
</section>

<section class="light-page-section contacts-light-page">
	<div class="site-container contacts-light-page__container">
		<div class="contacts-light-page__form">
			<?php
			(new \Components\ContactForm\ContactForm([
				'recipient' => 'lancelot.ozernuy@gmail.com',
				'theme' => 'Сообщение со страницы контактов LANCY',
			]))->render();
			?>
		</div>

		<div class="contacts-light-page__networks">
			<?php (new \Components\SocialNetworks\SocialNetworks())->render(); ?>
		</div>
		<div class="contacts-light-page__direct">
			<a href="tel:89205201831">8 (920) 520 18 31</a>
			<a href="mailto:lancelot.ozernuy@gmail.com">lancelot.ozernuy@gmail.com</a>
		</div>
	</div>
</section>
