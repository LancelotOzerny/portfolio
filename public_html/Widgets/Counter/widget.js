(() => {
	document.querySelectorAll('[data-widget="counter"]').forEach((root) => {
		const button = root.querySelector('.widget-counter');
		if (!(button instanceof HTMLElement) || button.dataset.widgetReady === '1') {
			return;
		}

		button.dataset.widgetReady = '1';
		let value = 1;

		button.addEventListener('click', () => {
			value += 1;
			button.textContent = String(value);
		});
	});
})();
