(function () {
	const carousels = document.querySelectorAll('[data-blog-carousel]');
	if (!carousels.length) {
		return;
	}

	carousels.forEach(function (carousel) {
		const viewport = carousel.querySelector('.blog-carousel__viewport');
		const track = carousel.querySelector('.blog-carousel__track');
		const prevButton = carousel.querySelector('.blog-carousel__nav_prev');
		const nextButton = carousel.querySelector('.blog-carousel__nav_next');

		if (!viewport || !track || !prevButton || !nextButton) {
			return;
		}

		let offset = 0;

		function getStep() {
			const card = track.querySelector('.blog-carousel-card');
			if (!card) {
				return viewport.clientWidth;
			}

			const styles = window.getComputedStyle(track);
			const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;

			return card.getBoundingClientRect().width + gap;
		}

		function getMaxOffset() {
			return Math.max(0, track.scrollWidth - viewport.clientWidth);
		}

		function updateButtons() {
			prevButton.disabled = offset <= 0;
			nextButton.disabled = offset >= getMaxOffset();
		}

		function applyOffset() {
			const maxOffset = getMaxOffset();
			offset = Math.max(0, Math.min(offset, maxOffset));
			track.style.transform = 'translateX(' + (-offset) + 'px)';
			updateButtons();
		}

		prevButton.addEventListener('click', function () {
			offset -= getStep();
			applyOffset();
		});

		nextButton.addEventListener('click', function () {
			offset += getStep();
			applyOffset();
		});

		window.addEventListener('resize', applyOffset);
		applyOffset();
	});
})();
