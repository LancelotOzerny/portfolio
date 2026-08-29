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

		if (event.target.closest('[data-component-edit-close]')) {
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

		if (event.target.closest('[data-open-include-area]')) {
			if (activeSettingsComponent) {
				const component = activeSettingsComponent;
				closeModal(settingsModal);
				openIncludeAreaModal(component);
			}
			return;
		}

		if (event.target.closest('[data-open-image-gallery]')) {
			if (activeSettingsComponent) {
				const component = activeSettingsComponent;
				closeModal(settingsModal);
				openImageModal(component);
			}
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
			'Выбор фото из галереи',
			'<div class="component-edit-modal__gallery"></div><div class="component-edit-modal__message"></div>',
			'<button class="component-edit-modal__button" type="button" data-component-edit-close>Отмена</button>' +
			'<button class="component-edit-modal__button component-edit-modal__button_primary" type="button" data-image-save>Выбрать</button>'
		);

		imageGrid = imageModal.querySelector('.component-edit-modal__gallery');
		imageMessage = imageModal.querySelector('.component-edit-modal__message');
	}

	function parseComponentParams(component) {
		try {
			return JSON.parse(component.dataset.componentParams || '{}');
		} catch (error) {
			return {};
		}
	}

	function parseComponentTemplates(component) {
		const raw = (component.getAttribute('data-component-templates') || '').trim();
		if (raw === '') {
			return ['Default'];
		}

		return raw.split(',')
			.map(function (item) {
				return item.trim();
			})
			.filter(function (item) {
				return item !== '';
			});
	}

	function getParamLabel(key) {
		if (key === 'template') {
			return 'Шаблон компонента';
		}

		return key;
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

	function buildTemplateField(component, currentValue) {
		const templates = parseComponentTemplates(component);
		const selected = String(currentValue || 'Default');
		const optionsList = templates.slice();

		if (selected && optionsList.indexOf(selected) === -1) {
			optionsList.unshift(selected);
		}

		if (optionsList.length === 0) {
			optionsList.push('Default');
		}

		const options = optionsList.map(function (templateName) {
			const name = String(templateName);
			return '<option value="' + escapeHtml(name) + '"' + (name === selected ? ' selected' : '') + '>'
				+ escapeHtml(name)
				+ '</option>';
		}).join('');

		return [
			'<label class="component-edit-modal__field">',
			'<span class="component-edit-modal__label">' + escapeHtml(getParamLabel('template')) + '</span>',
			'<select class="component-edit-modal__input component-edit-modal__select" name="template" data-param-type="string">',
			options,
			'</select>',
			'</label>',
		].join('');
	}

	function buildSettingsField(key, value, component) {
		if (key === 'template') {
			return buildTemplateField(component, value);
		}

		const type = getParamType(value);
		const label = getParamLabel(key);

		if (type === 'boolean') {
			return [
				'<label class="component-edit-modal__field component-edit-modal__field_checkbox">',
				'<input class="component-edit-modal__checkbox" type="checkbox" name="' + escapeHtml(key) + '" data-param-type="boolean"' + (value ? ' checked' : '') + '>',
				'<span class="component-edit-modal__label">' + escapeHtml(label) + '</span>',
				'</label>',
			].join('');
		}

		if (type === 'number') {
			return [
				'<label class="component-edit-modal__field">',
				'<span class="component-edit-modal__label">' + escapeHtml(label) + '</span>',
				'<input class="component-edit-modal__input" type="number" name="' + escapeHtml(key) + '" data-param-type="number" value="' + escapeHtml(value) + '">',
				'</label>',
			].join('');
		}

		return [
			'<label class="component-edit-modal__field">',
			'<span class="component-edit-modal__label">' + escapeHtml(label) + '</span>',
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

	function buildSpecialActions(component) {
		const type = component.dataset.componentType;

		if (type === 'IncludeArea') {
			return '<div class="component-edit-modal__actions">'
				+ '<button class="component-edit-modal__button" type="button" data-open-include-area>Редактировать содержимое</button>'
				+ '</div>';
		}

		if (type === 'ImagePreview') {
			return '<div class="component-edit-modal__actions">'
				+ '<button class="component-edit-modal__button" type="button" data-open-image-gallery>Выбрать из галереи</button>'
				+ '</div>';
		}

		return '';
	}

	function openSettingsModal(component) {
		ensureSettingsModal();
		activeSettingsComponent = component;
		settingsMessage.textContent = '';

		const params = parseComponentParams(component);
		const orderedKeys = Object.keys(params).filter(function (key) {
			return key !== 'template';
		});
		orderedKeys.unshift('template');

		const fields = orderedKeys.map(function (key) {
			const value = Object.prototype.hasOwnProperty.call(params, key)
				? params[key]
				: (key === 'template' ? 'Default' : '');
			return buildSettingsField(key, value, component);
		}).join('');

		settingsForm.innerHTML = fields + buildSpecialActions(component);

		settingsModal.classList.add('is-open');
		const firstField = settingsForm.querySelector('select, input');
		if (firstField) {
			firstField.focus();
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

	function renderImageGrid(albums) {
		imageGrid.innerHTML = '';

		if (!albums.length) {
			imageGrid.innerHTML = '<p>В фотогалерее пока нет изображений. Добавьте их в разделе «Контент → Галерея».</p>';
			return;
		}

		albums.forEach(function (album) {
			const section = document.createElement('section');
			section.className = 'component-edit-modal__gallery-album';

			const title = document.createElement('h3');
			title.className = 'component-edit-modal__gallery-title';
			title.textContent = album.name;
			section.appendChild(title);

			const grid = document.createElement('div');
			grid.className = 'component-edit-modal__images';

			(album.photos || []).forEach(function (item) {
				const button = document.createElement('button');
				button.type = 'button';
				button.className = 'component-edit-modal__image-item';
				button.dataset.imagePath = item.path;
				button.innerHTML = [
					'<img src="' + item.path + '" alt="" loading="lazy" decoding="async">',
					'<span class="component-edit-modal__image-name">' + item.name + '</span>',
				].join('');

				button.addEventListener('click', function () {
					imageGrid.querySelectorAll('.component-edit-modal__image-item').forEach(function (node) {
						node.classList.remove('is-selected');
					});
					button.classList.add('is-selected');
					selectedImagePath = item.path;
				});

				grid.appendChild(button);
			});

			section.appendChild(grid);
			imageGrid.appendChild(section);
		});
	}

	function openImageModal(component) {
		ensureImageModal();
		activeImageComponent = component;
		selectedImagePath = parseComponentParams(component).path || '';
		imageMessage.textContent = 'Загрузка...';
		imageGrid.innerHTML = '';
		imageModal.classList.add('is-open');

		fetch('/api/gallery/')
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (!result.success) {
					throw new Error(result.message || 'Не удалось загрузить галерею.');
				}

				renderImageGrid(result.albums || []);
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
