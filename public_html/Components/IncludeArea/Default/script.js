(function () {
	let modal = null;
	let textarea = null;
	let message = null;
	let activeArea = null;

	function ensureModal() {
		if (modal) {
			return;
		}

		modal = document.createElement('div');
		modal.className = 'include-area-modal';
		modal.innerHTML = [
			'<div class="include-area-modal__dialog" role="dialog" aria-modal="true" aria-label="Редактирование области">',
			'<div class="include-area-modal__header">',
			'<h2 class="include-area-modal__title">Редактирование области</h2>',
			'<button class="include-area-modal__button" type="button" data-include-area-close>Закрыть</button>',
			'</div>',
			'<div class="include-area-modal__body">',
			'<textarea class="include-area-modal__textarea" spellcheck="false"></textarea>',
			'<div class="include-area-modal__message"></div>',
			'</div>',
			'<div class="include-area-modal__footer">',
			'<button class="include-area-modal__button" type="button" data-include-area-close>Отмена</button>',
			'<button class="include-area-modal__button include-area-modal__button_primary" type="button" data-include-area-save>Сохранить</button>',
			'</div>',
			'</div>',
		].join('');

		document.body.appendChild(modal);
		textarea = modal.querySelector('.include-area-modal__textarea');
		message = modal.querySelector('.include-area-modal__message');
		modal.addEventListener('click', handleModalClick);
	}

	function handleModalClick(event) {
		if (event.target === modal || event.target.closest('[data-include-area-close]')) {
			closeModal();
			return;
		}

		if (event.target.closest('[data-include-area-save]')) {
			saveActiveArea();
		}
	}

	function openModal(area) {
		ensureModal();
		activeArea = area;
		textarea.value = area.innerHTML.trim();
		message.textContent = '';
		modal.classList.add('is-open');
		textarea.focus();
	}

	function closeModal() {
		if (!modal) {
			return;
		}

		modal.classList.remove('is-open');
		activeArea = null;
	}

	function saveActiveArea() {
		if (!activeArea) {
			return;
		}

		message.textContent = 'Сохраняю...';

		fetch('/api/include-area/save/', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				path: activeArea.dataset.includeAreaPath,
				content: textarea.value,
			}),
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (!result.success) {
					throw new Error(result.message || 'Не удалось сохранить область.');
				}

				activeArea.innerHTML = textarea.value;
				message.textContent = 'Сохранено.';
				setTimeout(closeModal, 500);
			})
			.catch(function (error) {
				message.textContent = error.message;
			});
	}

	document.addEventListener('dblclick', function (event) {
		const area = event.target.closest('.include-area_editable');
		if (!area) {
			return;
		}

		event.preventDefault();
		openModal(area);
	});
})();
