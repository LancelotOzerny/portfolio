<?php
/**
 * Модалка публикации статьи из AdminBar.
 *
 * @var int $articleId
 * @var string $scheduleInputValue
 * @var string $backUrl
 */

$articleId = (int) ($articleId ?? 0);
$scheduleInputValue = (string) ($scheduleInputValue ?? '');
$backUrl = (string) ($backUrl ?? '/');
?>

<form id="blog-publication-publish-form" action="/admin/content/blog/articles/<?= $articleId ?>/publish/" method="post" hidden>
	<input type="hidden" name="back" value="<?= htmlspecialchars($backUrl) ?>">
</form>

<div class="blog-settings-modal" id="blog-publication-modal-schedule" hidden>
	<div class="blog-settings-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="blog-publication-schedule-title">
		<div class="blog-settings-modal__header">
			<h2 class="blog-settings-modal__title" id="blog-publication-schedule-title">Опубликовать потом</h2>
			<button class="blog-settings-modal__close" type="button" data-blog-publication-close aria-label="Закрыть">×</button>
		</div>
		<form class="blog-settings-modal__form" action="/admin/content/blog/articles/<?= $articleId ?>/schedule/" method="post">
			<input type="hidden" name="back" value="<?= htmlspecialchars($backUrl) ?>">
			<label class="blog-settings-modal__label" for="blog-publication-schedule-at">Время публикации</label>
			<input
				class="blog-settings-modal__input"
				id="blog-publication-schedule-at"
				name="published_at"
				type="datetime-local"
				value="<?= htmlspecialchars($scheduleInputValue) ?>"
				required
			>
			<div class="blog-settings-modal__actions">
				<button class="blog-settings-modal__button blog-settings-modal__button_secondary" type="button" data-blog-publication-close>Отмена</button>
				<button class="blog-settings-modal__button" type="submit">Запланировать</button>
			</div>
		</form>
	</div>
</div>

<script>
(function () {
	const scheduleModal = document.getElementById('blog-publication-modal-schedule');
	const publishForm = document.getElementById('blog-publication-publish-form');
	const publishButtons = document.querySelectorAll('[data-blog-publish]');
	const scheduleButtons = document.querySelectorAll('[data-blog-schedule-open]');

	if (!scheduleModal && !publishButtons.length) {
		return;
	}

	const closeScheduleModal = () => {
		if (!scheduleModal) {
			return;
		}

		scheduleModal.hidden = true;
		scheduleModal.classList.remove('is-open');
		document.body.classList.remove('blog-settings-modal-open');
	};

	const openScheduleModal = () => {
		if (!scheduleModal) {
			return;
		}

		scheduleModal.hidden = false;
		scheduleModal.classList.add('is-open');
		document.body.classList.add('blog-settings-modal-open');
		const focusTarget = scheduleModal.querySelector('input, button');
		if (focusTarget instanceof HTMLElement) {
			focusTarget.focus();
		}
	};

	publishButtons.forEach((button) => {
		button.addEventListener('click', () => {
			if (!publishForm) {
				return;
			}

			if (!window.confirm('Опубликовать статью сейчас?')) {
				return;
			}

			publishForm.submit();
		});
	});

	scheduleButtons.forEach((button) => {
		button.addEventListener('click', openScheduleModal);
	});

	document.addEventListener('click', (event) => {
		if (event.target.closest('[data-blog-publication-close]')) {
			closeScheduleModal();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeScheduleModal();
		}
	});
})();
</script>
