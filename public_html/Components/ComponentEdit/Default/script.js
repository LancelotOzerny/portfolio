(function () {
	let includeAreaModal = null;
	let includeAreaTextarea = null;
	let includeAreaMessage = null;
	let activeIncludeArea = null;

	let settingsModal = null;
	let settingsForm = null;
	let settingsMessage = null;
	let activeSettingsComponent = null;

	let imageModal = null;
	let imageGrid = null;
	let imageMessage = null;
	let activeImageComponent = null;
	let selectedImagePath = '';

	function createModal(className, dialogClass, title, bodyHtml, footerHtml) {
		const modal = document.createElement('div');
		modal.className = 'component-edit-modal ' + className;
		modal.innerHTML = [
			'<div class="component-edit-modal__dialog' + (dialogClass ? ' ' + dialogClass : '') + '" role="dialog" aria-modal="true">',
			'<div class="component-edit-modal__header">',
			'<h2 class="component-edit-modal__title">' + title + '</h2>',
			'<button class="component-edit-modal__button" type="button" data-component-edit-close>Закрыть</button>',
			'</div>',
			'<div class="component-edit-modal__body">' + bodyHtml + '</div>',
			'<div class="component-edit-modal__footer">' + footerHtml + '</div>',
			'</div>',
		].join('');

		document.body.appendChild(modal);
		modal.addEventListener('click', handleModalClick);

		return modal;
	}

	function handleModalClick(event) {
		const modal = event.currentTarget;

		if (event.target === modal || event.target.closest('[data-component-edit-close]')) {
			closeModal(modal);
			return;
		}

		if (event.target.closest('[data-include-area-save]')) {
			saveIncludeArea();
			return;
		}

		if (event.target.closest('[data-settings-save]')) {
			saveSettings();
			return;
		}

		if (event.target.closest('[data-image-save]')) {
			saveImageSelection();
		}
	}

	function closeModal(modal) {
		modal.classList.remove('is-open');

		if (modal === includeAreaModal) {
			activeIncludeArea = null;
		}

		if (modal === settingsModal) {
			activeSettingsComponent = null;
		}

		if (modal === imageModal) {
			activeImageComponent = null;
			selectedImagePath = '';
		}
	}

	function ensureIncludeAreaModal() {
		if (includeAreaModal) {
			return;
		}

		includeAreaModal = createModal(
			'component-edit-modal_include-area',
			'',
			'Редактирование области',
			'<textarea class="component-edit-modal__textarea" spellcheck="false"></textarea><div class="component-edit-modal__message"></div>',
			'<button class="component-edit-modal__button" type="button" data-component-edit-close>Отмена</button>' +
			'<button class="component-edit-modal__button component-edit-modal__button_primary" type="button" data-include-area-save>Сохранить</button>'
		);

		includeAreaTextarea = includeAreaModal.querySelector('.component-edit-modal__textarea');
		includeAreaMessage = includeAreaModal.querySelector('.component-edit-modal__message');
	}

	function ensureSettingsModal() {
		if (settingsModal) {
			return;
		}

		settingsModal = createModal(
			'component-edit-modal_settings',
			'',
			'Настройки компонента',
			'<form class="component-edit-modal__settings-form"></form><div class="component-edit-modal__message"></div>',
			'<button class="component-edit-modal__button" type="button" data-component-edit-close>Отмена</button>' +
			'<button class="component-edit-modal__button component-edit-modal__button_primary" type="button" data-settings-save>Сохранить</button>'
		);

		settingsForm = settingsModal.querySelector('.component-edit-modal__settings-form');
		settingsMessage = settingsModal.querySelector('.component-edit-modal__message');
	}

	function ensureImageModal() {
		if (imageModal) {
			return;
		}

		imageModal = createModal(
			'component-edit-modal_image',
			'component-edit-modal__dialog_wide',
			'Выбор изображения',
			'<div class="component-edit-modal__images"></div><div class="component-edit-modal__message"></div>',
			'<button class="component-edit-modal__button" type="button" data-component-edit-close>Отмена</button>' +
			'<button class="component-edit-modal__button component-edit-modal__button_primary" type="button" data-image-save>Выбрать</button>'
		);

		imageGrid = imageModal.querySelector('.component-edit-modal__images');
		imageMessage = imageModal.querySelector('.component-edit-modal__message');
	}

	function parseComponentParams(component) {
		try {
			return JSON.parse(component.dataset.componentParams || '{}');
		} catch (error) {
			return {};
		}
	}

	function getComponentContent(component) {
		return component.querySelector('.component-edit__content');
	}

	function getParamType(value) {
		if (typeof value === 'boolean') {
			return 'boolean';
		}

		if (typeof value === 'number') {
			return 'number';
		}

		return 'string';
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	}

	function openIncludeAreaModal(component) {
		ensureIncludeAreaModal();
		activeIncludeArea = component;

		const content = getComponentContent(component);
		includeAreaTextarea.value = content ? content.innerHTML.trim() : '';
		includeAreaMessage.textContent = '';
		includeAreaModal.classList.add('is-open');
		includeAreaTextarea.focus();
	}

	function saveIncludeArea() {
		if (!activeIncludeArea) {
			return;
		}

		const content = getComponentContent(activeIncludeArea);
		if (!content) {
			return;
		}

		includeAreaMessage.textContent = 'Сохраняю...';

		fetch('/api/include-area/save/', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				path: activeIncludeArea.dataset.includeAreaPath,
				content: includeAreaTextarea.value,
			}),
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (!result.success) {
					throw new Error(result.message || 'Не удалось сохранить область.');
				}

				content.innerHTML = includeAreaTextarea.value;
				includeAreaMessage.textContent = 'Сохранено.';
				setTimeout(function () {
					closeModal(includeAreaModal);
				}, 500);
			})
			.catch(function (error) {
				includeAreaMessage.textContent = error.message;
			});
	}

	function buildSettingsField(key, value) {
		const type = getParamType(value);

		if (type === 'boolean') {
			return [
				'<label class="component-edit-modal__field component-edit-modal__field_checkbox">',
				'<input class="component-edit-modal__checkbox" type="checkbox" name="' + escapeHtml(key) + '" data-param-type="boolean"' + (value ? ' checked' : '') + '>',
				'<span class="component-edit-modal__label">' + escapeHtml(key) + '</span>',
				'</label>',
			].join('');
		}

		if (type === 'number') {
			return [
				'<label class="component-edit-modal__field">',
				'<span class="component-edit-modal__label">' + escapeHtml(key) + '</span>',
				'<input class="component-edit-modal__input" type="number" name="' + escapeHtml(key) + '" data-param-type="number" value="' + escapeHtml(value) + '">',
				'</label>',
			].join('');
		}

		return [
			'<label class="component-edit-modal__field">',
			'<span class="component-edit-modal__label">' + escapeHtml(key) + '</span>',
			'<input class="component-edit-modal__input" type="text" name="' + escapeHtml(key) + '" data-param-type="string" value="' + escapeHtml(value == null ? '' : value) + '">',
			'</label>',
		].join('');
	}

	function collectSettingsParams() {
		const params = {};

		settingsForm.querySelectorAll('[name]').forEach(function (input) {
			const type = input.dataset.paramType || 'string';

			if (type === 'boolean') {
				params[input.name] = input.checked;
				return;
			}

			if (type === 'number') {
				params[input.name] = input.value === '' ? 0 : Number(input.value);
				return;
			}

			params[input.name] = input.value;
		});

		return params;
	}

	function openSettingsModal(component) {
		ensureSettingsModal();
		activeSettingsComponent = component;
		settingsMessage.textContent = '';

		const params = parseComponentParams(component);
		const entries = Object.entries(params);

		if (entries.length === 0) {
			settingsForm.innerHTML = '<p>У компонента нет редактируемых настроек.</p>';
		} else {
			settingsForm.innerHTML = entries.map(function (entry) {
				return buildSettingsField(entry[0], entry[1]);
			}).join('');
		}

		settingsModal.classList.add('is-open');
		const firstInput = settingsForm.querySelector('input');
		if (firstInput) {
			firstInput.focus();
		}
	}

	function saveSettings() {
		if (!activeSettingsComponent) {
			return;
		}

		settingsMessage.textContent = 'Сохраняю...';

		const params = collectSettingsParams();

		fetch('/api/component/settings/save/', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				key: activeSettingsComponent.dataset.componentKey,
				params: params,
			}),
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (!result.success) {
					throw new Error(result.message || 'Не удалось сохранить настройки.');
				}

				window.location.reload();
			})
			.catch(function (error) {
				settingsMessage.textContent = error.message;
			});
	}

	function renderImageGrid(items) {
		imageGrid.innerHTML = '';

		if (!items.length) {
			imageGrid.innerHTML = '<p>Изображения не найдены в /upload.</p>';
			return;
		}

		items.forEach(function (item) {
			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'component-edit-modal__image-item';
			button.dataset.imagePath = item.path;
			button.innerHTML = [
				'<img src="' + item.path + '" alt="">',
				'<span class="component-edit-modal__image-name">' + item.name + '</span>',
			].join('');

			button.addEventListener('click', function () {
				imageGrid.querySelectorAll('.component-edit-modal__image-item').forEach(function (node) {
					node.classList.remove('is-selected');
				});
				button.classList.add('is-selected');
				selectedImagePath = item.path;
			});

			imageGrid.appendChild(button);
		});
	}

	function openImageModal(component) {
		ensureImageModal();
		activeImageComponent = component;
		selectedImagePath = parseComponentParams(component).path || '';
		imageMessage.textContent = 'Загрузка...';
		imageGrid.innerHTML = '';
		imageModal.classList.add('is-open');

		fetch('/api/images/')
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (!result.success) {
					throw new Error(result.message || 'Не удалось загрузить изображения.');
				}

				renderImageGrid(result.items || []);
				imageMessage.textContent = '';

				if (selectedImagePath) {
					const selected = imageGrid.querySelector('[data-image-path="' + selectedImagePath + '"]');
					if (selected) {
						selected.classList.add('is-selected');
					}
				}
			})
			.catch(function (error) {
				imageMessage.textContent = error.message;
			});
	}

	function saveImageSelection() {
		if (!activeImageComponent || !selectedImagePath) {
			imageMessage.textContent = 'Выберите изображение.';
			return;
		}

		imageMessage.textContent = 'Сохраняю...';

		const params = parseComponentParams(activeImageComponent);
		params.path = selectedImagePath;

		fetch('/api/component/settings/save/', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				key: activeImageComponent.dataset.componentKey,
				params: params,
			}),
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (!result.success) {
					throw new Error(result.message || 'Не удалось сохранить изображение.');
				}

				window.location.reload();
			})
			.catch(function (error) {
				imageMessage.textContent = error.message;
			});
	}

	function openComponentEditor(component) {
		const type = component.dataset.componentType;

		if (type === 'IncludeArea') {
			openIncludeAreaModal(component);
			return;
		}

		if (type === 'ImagePreview') {
			openImageModal(component);
			return;
		}

		openSettingsModal(component);
	}

	document.addEventListener('click', function (event) {
		const trigger = event.target.closest('.component-edit__trigger');
		if (!trigger) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();

		const component = trigger.closest('.component-edit');
		if (!component) {
			return;
		}

		openComponentEditor(component);
	});

	document.addEventListener('dblclick', function (event) {
		const component = event.target.closest('.component-edit');
		if (!component) {
			return;
		}

		if (event.target.closest('.component-edit__trigger')) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();
		openComponentEditor(component);
	});
})();
