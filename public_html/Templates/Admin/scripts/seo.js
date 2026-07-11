(function () {
	const root = document.querySelector('[data-seo-editor]');
	const form = document.querySelector('[data-seo-form]');
	if (!root || !form) {
		return;
	}

	const siteName = root.dataset.siteName || '';
	const defaultTitle = root.dataset.defaultTitle || '';
	const defaultDescription = root.dataset.defaultDescription || '';
	const pagePath = root.dataset.pagePath || '/';
	const domain = root.dataset.domain || '';
	const defaultOgImage = root.dataset.defaultOgImage || '';

	const fields = {
		title: form.querySelector('[data-seo-title]'),
		description: form.querySelector('[data-seo-description]'),
		canonical: form.querySelector('[data-seo-canonical]'),
		ogTitle: form.querySelector('[data-seo-og-title]'),
		ogDescription: form.querySelector('[data-seo-og-description]'),
		ogImage: form.querySelector('[data-seo-og-image]'),
	};

	const counters = {
		title: form.querySelector('[data-seo-title-count]'),
		description: form.querySelector('[data-seo-description-count]'),
	};

	const preview = {
		searchTitle: root.querySelector('[data-seo-preview-search-title]'),
		searchUrl: root.querySelector('[data-seo-preview-search-url]'),
		searchDescription: root.querySelector('[data-seo-preview-search-description]'),
		socialImage: root.querySelector('[data-seo-preview-social-image]'),
		socialSite: root.querySelector('[data-seo-preview-social-site]'),
		socialTitle: root.querySelector('[data-seo-preview-social-title]'),
		socialDescription: root.querySelector('[data-seo-preview-social-description]'),
	};

	function pickValue(input, fallback) {
		const value = (input && input.value ? input.value : '').trim();
		return value !== '' ? value : fallback;
	}

	function applyTitleTemplate(title) {
		if (title === '' || siteName === '') {
			return title || siteName;
		}

		const suffix = ' — ' + siteName;
		if (title.endsWith(suffix) || title === siteName) {
			return title;
		}

		return title + suffix;
	}

	function buildCanonical() {
		const manual = pickValue(fields.canonical, '');
		if (manual !== '') {
			return manual;
		}

		const normalizedPath = pagePath === '/' ? '/' : pagePath.replace(/\/$/, '');
		return domain + normalizedPath;
	}

	function updateCounters() {
		if (counters.title && fields.title) {
			counters.title.textContent = String(fields.title.value.length);
		}
		if (counters.description && fields.description) {
			counters.description.textContent = String(fields.description.value.length);
		}
	}

	function updatePreview() {
		const rawTitle = pickValue(fields.title, defaultTitle);
		const title = applyTitleTemplate(rawTitle);
		const description = pickValue(fields.description, defaultDescription);
		const canonical = buildCanonical();
		const ogTitle = pickValue(fields.ogTitle, title);
		const ogDescription = pickValue(fields.ogDescription, description);
		const ogImage = pickValue(fields.ogImage, defaultOgImage);

		if (preview.searchTitle) preview.searchTitle.textContent = title;
		if (preview.searchUrl) preview.searchUrl.textContent = canonical;
		if (preview.searchDescription) preview.searchDescription.textContent = description;
		if (preview.socialSite) preview.socialSite.textContent = siteName;
		if (preview.socialTitle) preview.socialTitle.textContent = ogTitle;
		if (preview.socialDescription) preview.socialDescription.textContent = ogDescription;
		if (preview.socialImage) {
			preview.socialImage.style.backgroundImage = ogImage !== '' ? 'url(' + ogImage + ')' : 'none';
		}

		updateCounters();
	}

	Object.values(fields).forEach(function (field) {
		if (!field) return;
		field.addEventListener('input', updatePreview);
	});

	updatePreview();
})();
