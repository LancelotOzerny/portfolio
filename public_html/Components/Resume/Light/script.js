(function () {
	'use strict';

	document.querySelectorAll('[data-resume-light]').forEach(function (root) {
		root.addEventListener('click', function (event) {
			var header = event.target.closest('.resume-light__header');

			if (!header || !root.contains(header)) {
				return;
			}

			var item = header.closest('.resume-light__item');
			var isOpen = item.classList.toggle('is-open');

			header.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});
	});
})();
