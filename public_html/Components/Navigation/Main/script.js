(function () {
	'use strict';

	var navigation = document.querySelector('[data-main-navigation]');

	if (!navigation) {
		return;
	}

	var toggle = navigation.querySelector('[data-nav-toggle]');
	var panel = navigation.querySelector('.main-navigation__panel');
	var mobileQuery = window.matchMedia('(max-width: 760px)');
	var collapseOffset = 250;
	var ticking = false;
	var raf = window.requestAnimationFrame || function (callback) {
		return window.setTimeout(callback, 16);
	};

	function setExpanded(isExpanded) {
		navigation.classList.toggle('is-open', isExpanded);

		if (toggle) {
			toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
			toggle.setAttribute('aria-label', isExpanded ? 'Close menu' : 'Open menu');
		}
	}

	function updateMobileState() {
		navigation.classList.toggle('is-mobile', mobileQuery.matches);

		if (!mobileQuery.matches && !navigation.classList.contains('is-collapsed')) {
			setExpanded(false);
		}
	}

	function updateNavigation() {
		var currentScroll = window.pageYOffset || document.documentElement.scrollTop || 0;
		var isScrolled = currentScroll > 12;
		var isCollapsed = currentScroll > collapseOffset;

		navigation.classList.toggle('is-scrolled', isScrolled);
		navigation.classList.toggle('is-collapsed', isCollapsed);

		if (!isCollapsed && !mobileQuery.matches) {
			setExpanded(false);
		}

		ticking = false;
	}

	function requestUpdate() {
		if (!ticking) {
			raf(updateNavigation);
			ticking = true;
		}
	}

	if (toggle) {
		toggle.addEventListener('click', function () {
			setExpanded(!navigation.classList.contains('is-open'));
		});
	}

	document.addEventListener('click', function (event) {
		if (!navigation.classList.contains('is-open') || navigation.contains(event.target)) {
			return;
		}

		setExpanded(false);
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			setExpanded(false);
		}
	});

	if (panel) {
		panel.addEventListener('click', function (event) {
			if (event.target.closest('a')) {
				setExpanded(false);
			}
		});
	}

	if (typeof mobileQuery.addEventListener === 'function') {
		mobileQuery.addEventListener('change', updateMobileState);
	} else if (typeof mobileQuery.addListener === 'function') {
		mobileQuery.addListener(updateMobileState);
	}

	updateMobileState();
	updateNavigation();
	window.addEventListener('scroll', requestUpdate, { passive: true });
	window.addEventListener('resize', function () {
		updateMobileState();
		requestUpdate();
	});
})();
