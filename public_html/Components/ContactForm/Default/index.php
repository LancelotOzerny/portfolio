<form class="contact-form" data-contact-form>
	<input type="hidden" name="recipient" value="<?= htmlspecialchars((string) $this->getParam('recipient')) ?>">
	<input type="hidden" name="theme" value="<?= htmlspecialchars((string) $this->getParam('theme')) ?>">
	<input type="hidden" name="form_hash" value="<?= htmlspecialchars((string) $this->getParam('form_hash')) ?>">

	<div class="contact-form__row">
		<label class="contact-form__field">
			<span>Имя</span>
			<input type="text" name="name" autocomplete="name" required>
		</label>

		<label class="contact-form__field">
			<span>Email</span>
			<input type="email" name="email" autocomplete="email" required>
		</label>
	</div>

	<label class="contact-form__field">
		<span>Сообщение</span>
		<textarea name="message" rows="5" required></textarea>
	</label>

	<div class="contact-form__footer">
		<button class="button button_primary contact-form__submit" type="submit">Отправить</button>
		<p class="contact-form__status" data-contact-form-status aria-live="polite"></p>
	</div>
</form>
