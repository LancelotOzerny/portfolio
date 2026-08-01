(function () {
	'use strict';

	var closeMs = 250;

	document.querySelectorAll('[data-resume-light]').forEach(function (root) {
		var activeId = null;
		var isAnimating = false;
		var pendingId = null;
		var closeTimer = null;

		function getTab(id) {
			return root.querySelector('[data-resume-tab="' + id + '"]');
		}

		function getPanel(id) {
			return root.querySelector('[data-resume-panel="' + id + '"]');
		}

		function setActiveTab(id) {
			root.querySelectorAll('[data-resume-tab]').forEach(function (tab) {
				var isActive = tab.getAttribute('data-resume-tab') === id;

				tab.classList.toggle('is-active', isActive);
				tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
			});
		}

		function openPanel(id) {
			var panel = getPanel(id);

			if (!panel) {
				isAnimating = false;
				return;
			}

			panel.hidden = false;
			panel.classList.remove('is-closing');
			setActiveTab(id);
			activeId = id;
			isAnimating = false;

			if (pendingId && pendingId !== activeId) {
				var nextId = pendingId;
				pendingId = null;
				switchTo(nextId);
			}
		}

		function closePanel(id, callback) {
			var panel = getPanel(id);

			if (!panel || panel.hidden) {
				if (callback) {
					callback();
				}
				return;
			}

			window.clearTimeout(closeTimer);
			panel.classList.add('is-closing');

			closeTimer = window.setTimeout(function () {
				panel.hidden = true;
				panel.classList.remove('is-closing');

				if (callback) {
					callback();
				}
			}, closeMs);
		}

		function switchTo(id) {
			if (id === activeId) {
				return;
			}

			if (isAnimating) {
				pendingId = id;
				return;
			}

			isAnimating = true;

			if (activeId) {
				var previousId = activeId;
				activeId = null;
				setActiveTab(null);
				closePanel(previousId, function () {
					openPanel(id);
				});
				return;
			}

			openPanel(id);
		}

		root.addEventListener('click', function (event) {
			var tab = event.target.closest('[data-resume-tab]');

			if (!tab || !root.contains(tab)) {
				return;
			}

			switchTo(tab.getAttribute('data-resume-tab'));
		});
	});
})();
