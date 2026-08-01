(function () {
	'use strict';

	var navigation = document.querySelector('[data-college-navigation]');

	if (!navigation) {
		return;
	}

	var toggle = navigation.querySelector('[data-college-nav-toggle]');
	var dropdownToggles = navigation.querySelectorAll('[data-college-dropdown-toggle]');
	var mobileQuery = window.matchMedia('(max-width: 860px)');

	function setNavigationOpen(isOpen) {
		navigation.classList.toggle('is-open', isOpen);

		if (toggle) {
			toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			toggle.setAttribute('aria-label', isOpen ? 'Закрыть меню' : 'Открыть меню');
		}
	}

	function closeDropdowns(exceptItem) {
		dropdownToggles.forEach(function (button) {
			var item = button.closest('.college-navigation__item_dropdown');

			if (!item || item === exceptItem) {
				return;
			}

			item.classList.remove('is-open');
			button.setAttribute('aria-expanded', 'false');
		});
	}

	if (toggle) {
		toggle.addEventListener('click', function () {
			setNavigationOpen(!navigation.classList.contains('is-open'));
		});
	}

	dropdownToggles.forEach(function (button) {
		button.addEventListener('click', function () {
			var item = button.closest('.college-navigation__item_dropdown');

			if (!item || !mobileQuery.matches) {
				return;
			}

			var isOpen = !item.classList.contains('is-open');
			closeDropdowns(item);
			item.classList.toggle('is-open', isOpen);
			button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});
	});

	document.addEventListener('click', function (event) {
		if (navigation.contains(event.target)) {
			return;
		}

		setNavigationOpen(false);
		closeDropdowns();
	});

	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Escape') {
			return;
		}

		setNavigationOpen(false);
		closeDropdowns();
	});

	navigation.addEventListener('click', function (event) {
		if (!event.target.closest('a')) {
			return;
		}

		setNavigationOpen(false);
		closeDropdowns();
	});
})();
