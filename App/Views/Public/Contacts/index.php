<?php
\Modules\Main\AssetLoader::getInstance()->addJs('/Templates/Inner/scripts/feedback.js');
?>

<div class="container contacts-page">
	<section class="contacts-shell scroll-show-area">
		<div class="contacts-shell__intro">
			<h2>Свяжитесь со мной</h2>
			<p>Опишите задачу, сроки и ожидания. Я вернусь с понятными вопросами или предложением.</p>
		</div>

		<form id="message-form" class="contacts-form">
			<div class="contacts-form__row">
				<div class="contacts-form__field">
					<label for="name" class="form-label">Ваше имя</label>
					<input type="text" class="form-control" id="name" placeholder="Как к вам обращаться" required>
				</div>

				<div class="contacts-form__field">
					<label for="email" class="form-label">Email</label>
					<input type="email" class="form-control" id="email" placeholder="example@example.com" required>
				</div>
			</div>

			<div class="contacts-form__field">
				<label for="message" class="form-label">Сообщение</label>
				<textarea class="form-control" id="message" rows="6" placeholder="Расскажите о проекте, сайте или доработке..." required></textarea>
			</div>

			<button type="submit" class="btn contacts-form__submit">Отправить сообщение</button>
		</form>
	</section>

	<aside class="contacts-aside scroll-show-area">
		<div class="contacts-aside__card">
			<h3>Контакты</h3>
			<p>Липецк, Россия</p>
			<a href="tel:89205201831">8 (920) 520 18 31</a>
			<a href="mailto:lancelot.ozernuy@gmail.com">lancelot.ozernuy@gmail.com</a>
		</div>

		<div class="contacts-aside__links">
			<a href="https://github.com/LancelotOzerny/">GitHub</a>
			<a href="https://t.me/lalalancy">Telegram</a>
			<a href="https://vk.com/lancy2002">VK</a>
		</div>
	</aside>
</div>

<div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
	<div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="toast-header">
			<strong class="me-auto toast-title">Уведомление</strong>
			<small class="toast-time">Сейчас</small>
			<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
		</div>
		<div class="toast-body"></div>
	</div>
</div>
