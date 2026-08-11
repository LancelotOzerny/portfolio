<?php
/**
 * Кнопки и модалки настроек блога (режим редактирования).
 *
 * @var string $basicAction
 * @var string $seoAction
 * @var string $csrfToken
 * @var string $basicTitle
 * @var string $basicDescription
 * @var string $seoTitle
 * @var string $seoDescription
 * @var string $seoKeywords
 * @var string $basicTitleLabel
 * @var string $basicDescriptionLabel
 */

$basicAction = (string) ($basicAction ?? '');
$seoAction = (string) ($seoAction ?? '');
$csrfToken = (string) ($csrfToken ?? '');
$basicTitle = (string) ($basicTitle ?? '');
$basicDescription = (string) ($basicDescription ?? '');
$seoTitle = (string) ($seoTitle ?? '');
$seoDescription = (string) ($seoDescription ?? '');
$seoKeywords = (string) ($seoKeywords ?? '');
$basicTitleLabel = (string) ($basicTitleLabel ?? 'Название');
$basicDescriptionLabel = (string) ($basicDescriptionLabel ?? 'Описание');
?>

<div class="blog-settings-modal" id="blog-settings-modal-basic" hidden>
	<div class="blog-settings-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="blog-settings-basic-title">
		<div class="blog-settings-modal__header">
			<h2 class="blog-settings-modal__title" id="blog-settings-basic-title">Базовая информация</h2>
			<button class="blog-settings-modal__close" type="button" data-blog-settings-close aria-label="Закрыть">×</button>
		</div>
		<form class="blog-settings-modal__form" action="<?= htmlspecialchars($basicAction) ?>" method="post">
			<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
			<label class="blog-settings-modal__label" for="blog-settings-basic-name"><?= htmlspecialchars($basicTitleLabel) ?></label>
			<input
				class="blog-settings-modal__input"
				id="blog-settings-basic-name"
				name="title"
				type="text"
				maxlength="255"
				required
				value="<?= htmlspecialchars($basicTitle) ?>"
			>
			<label class="blog-settings-modal__label" for="blog-settings-basic-description"><?= htmlspecialchars($basicDescriptionLabel) ?></label>
			<textarea
				class="blog-settings-modal__textarea"
				id="blog-settings-basic-description"
				name="description"
				rows="5"
				maxlength="500"
			><?= htmlspecialchars($basicDescription) ?></textarea>
			<div class="blog-settings-modal__actions">
				<button class="blog-settings-modal__button blog-settings-modal__button_secondary" type="button" data-blog-settings-close>Отмена</button>
				<button class="blog-settings-modal__button" type="submit">Сохранить</button>
			</div>
		</form>
	</div>
</div>

<div class="blog-settings-modal" id="blog-settings-modal-seo" hidden>
	<div class="blog-settings-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="blog-settings-seo-title">
		<div class="blog-settings-modal__header">
			<h2 class="blog-settings-modal__title" id="blog-settings-seo-title">SEO</h2>
			<button class="blog-settings-modal__close" type="button" data-blog-settings-close aria-label="Закрыть">×</button>
		</div>
		<form class="blog-settings-modal__form" action="<?= htmlspecialchars($seoAction) ?>" method="post">
			<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
			<label class="blog-settings-modal__label" for="blog-settings-seo-browser-title">Заголовок в браузере</label>
			<input
				class="blog-settings-modal__input"
				id="blog-settings-seo-browser-title"
				name="title"
				type="text"
				maxlength="255"
				value="<?= htmlspecialchars($seoTitle) ?>"
				placeholder="<?= htmlspecialchars($basicTitle) ?>"
			>
			<label class="blog-settings-modal__label" for="blog-settings-seo-description">Описание</label>
			<textarea
				class="blog-settings-modal__textarea"
				id="blog-settings-seo-description"
				name="description"
				rows="4"
				maxlength="320"
				placeholder="<?= htmlspecialchars($basicDescription) ?>"
			><?= htmlspecialchars($seoDescription) ?></textarea>
			<label class="blog-settings-modal__label" for="blog-settings-seo-keywords">Ключевые слова</label>
			<input
				class="blog-settings-modal__input"
				id="blog-settings-seo-keywords"
				name="keywords"
				type="text"
				maxlength="500"
				value="<?= htmlspecialchars($seoKeywords) ?>"
				placeholder="слово1, слово2, слово3"
			>
			<div class="blog-settings-modal__actions">
				<button class="blog-settings-modal__button blog-settings-modal__button_secondary" type="button" data-blog-settings-close>Отмена</button>
				<button class="blog-settings-modal__button" type="submit">Сохранить</button>
			</div>
		</form>
	</div>
</div>

<script>
(function () {
	const openButtons = document.querySelectorAll('[data-blog-settings-open]');
	if (!openButtons.length) {
		return;
	}

	const modals = {
		basic: document.getElementById('blog-settings-modal-basic'),
		seo: document.getElementById('blog-settings-modal-seo'),
	};

	const closeModal = (modal) => {
		if (!modal) {
			return;
		}
		modal.hidden = true;
		modal.classList.remove('is-open');
		document.body.classList.remove('blog-settings-modal-open');
	};

	const openModal = (key) => {
		const modal = modals[key];
		if (!modal) {
			return;
		}
		Object.values(modals).forEach(closeModal);
		modal.hidden = false;
		modal.classList.add('is-open');
		document.body.classList.add('blog-settings-modal-open');
		const focusTarget = modal.querySelector('input, textarea, button');
		if (focusTarget instanceof HTMLElement) {
			focusTarget.focus();
		}
	};

	openButtons.forEach((button) => {
		button.addEventListener('click', () => {
			openModal(button.getAttribute('data-blog-settings-open') || '');
		});
	});

	document.querySelectorAll('.blog-settings-modal').forEach((modal) => {
		modal.addEventListener('click', (event) => {
			if (event.target.closest('[data-blog-settings-close]')) {
				closeModal(modal);
			}
		});
	});

	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') {
			return;
		}
		Object.values(modals).forEach(closeModal);
	});
})();
</script>
