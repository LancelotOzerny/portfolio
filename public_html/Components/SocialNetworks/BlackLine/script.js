(function () {
	const cards = document.querySelectorAll('.networks-line__card');
	const hoverQuery = window.matchMedia('(hover: hover) and (pointer: fine) and (min-width: 980px)');
	const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

	if (!cards.length) {
		return;
	}

	cards.forEach(function (card) {
		const target = card.querySelector('.networks-line__target');
		let timer = null;
		let previousEdge = '';

		if (!target) {
			return;
		}

		function randomBetween(min, max) {
			return min + Math.random() * Math.max(0, max - min);
		}

		function getNextEdge() {
			const edges = ['top', 'right', 'bottom', 'left'].filter(function (edge) {
				return edge !== previousEdge;
			});
			const edge = edges[Math.floor(Math.random() * edges.length)];

			previousEdge = edge;
			return edge;
		}

		function moveTarget() {
			const maxX = Math.max(0, card.clientWidth - target.offsetWidth);
			const maxY = Math.max(0, card.clientHeight - target.offsetHeight);
			const horizontalInset = Math.min(24, maxX / 2);
			const verticalInset = Math.min(24, maxY / 2);
			const edge = getNextEdge();
			let x = 0;
			let y = 0;

			if (edge === 'top') {
				x = randomBetween(horizontalInset, maxX - horizontalInset);
			} else if (edge === 'right') {
				x = maxX;
				y = randomBetween(verticalInset, maxY - verticalInset);
			} else if (edge === 'bottom') {
				x = randomBetween(horizontalInset, maxX - horizontalInset);
				y = maxY;
			} else {
				y = randomBetween(verticalInset, maxY - verticalInset);
			}

			target.style.transform = 'translate3d(' + Math.round(x) + 'px, ' + Math.round(y) + 'px, 0)';
		}

		function scheduleMove() {
			if (!card.classList.contains('is-target-active')) {
				return;
			}

			moveTarget();
			timer = window.setTimeout(scheduleMove, randomBetween(750, 1450));
		}

		function start() {
			if (!hoverQuery.matches || reducedMotionQuery.matches) {
				return;
			}

			window.clearTimeout(timer);
			card.classList.add('is-target-active');
			scheduleMove();
		}

		function stop() {
			window.clearTimeout(timer);
			card.classList.remove('is-target-active');
			previousEdge = '';
		}

		card.addEventListener('mouseenter', start);
		card.addEventListener('mouseleave', stop);
		card.addEventListener('focusin', start);
		card.addEventListener('focusout', stop);
	});
})();
