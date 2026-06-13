(function () {
	var areas = document.querySelectorAll('.scroll-show-area');

	if (!areas.length) {
		return;
	}

	if (!('IntersectionObserver' in window)) {
		areas.forEach(function (area) {
			area.classList.add('is-visible');
		});
		return;
	}

	var observer = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (!entry.isIntersecting) {
				return;
			}

			entry.target.classList.add('is-visible');
			observer.unobserve(entry.target);
		});
	}, {
		rootMargin: '0px 0px -12% 0px',
		threshold: .12
	});

	areas.forEach(function (area) {
		observer.observe(area);
	});
}());
