(function () {
	'use strict';

	const output = document.querySelector('[data-page-load-time]');
	if (!output) {
		return;
	}

	function showLoadTime() {
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				const seconds = performance.now() / 1000;
				output.textContent = seconds.toFixed(6).replace('.', ',') + ' с';
			});
		});
	}

	if (document.readyState === 'complete') {
		showLoadTime();
		return;
	}

	window.addEventListener('load', showLoadTime, {once: true});
})();
