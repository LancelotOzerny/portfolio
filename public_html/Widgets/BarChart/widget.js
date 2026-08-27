(() => {
	const SVG_NS = 'http://www.w3.org/2000/svg';
	const WIDTH = 640;
	const HEIGHT = 360;
	const TOP = 28;
	const RIGHT = 16;
	const BOTTOM = 52;

	const parseAmount = (value) => {
		const parsed = Number.parseFloat(String(value || '').replace(',', '.'));
		return Number.isFinite(parsed) ? parsed : NaN;
	};

	const niceMax = (max) => {
		if (max <= 0) {
			return 1;
		}

		const exp = Math.pow(10, Math.floor(Math.log10(max)));
		const n = max / exp;
		const nice = n <= 1 ? 1 : n <= 2 ? 2 : n <= 5 ? 5 : 10;
		return nice * exp;
	};

	class BarChartWidget {
		constructor(root) {
			this.root = root;
			this.titleEl = root.querySelector('.widget-chart__title');
			this.xlabelEl = root.querySelector('.widget-chart__xlabel');
			this.ylabelEl = root.querySelector('.widget-chart__ylabel');
			this.emptyEl = root.querySelector('.widget-chart__empty');
			this.svg = root.querySelector('.widget-chart__svg');
		}

		bind() {
			if (!this.svg || this.root.dataset.widgetReady === '1') {
				return;
			}

			this.root.dataset.widgetReady = '1';
			this.render(this.readParams());
		}

		readParams() {
			const raw = this.root.getAttribute('data-widget-params');
			let params = {};
			if (raw) {
				try {
					const parsed = JSON.parse(raw);
					if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
						params = parsed;
					}
				} catch (error) {
					params = {};
				}
			}

			const points = [];
			(Array.isArray(params.points) ? params.points : []).forEach((item, index) => {
				if (!item || typeof item !== 'object') {
					return;
				}

				const y = parseAmount(item.y);
				if (!Number.isFinite(y)) {
					return;
				}

				const x = String(item.x || '').trim();
				points.push({
					x: x !== '' ? x : String(index + 1),
					y: y
				});
			});

			return {
				title: String(params.title || '').trim(),
				xlabel: String(params.xlabel || '').trim(),
				ylabel: String(params.ylabel || '').trim(),
				points: points.slice(0, 24)
			};
		}

		setText(node, value) {
			if (!node) {
				return;
			}

			node.textContent = value;
			node.hidden = value === '';
		}

		render(params) {
			this.setText(this.titleEl, params.title);
			this.setText(this.xlabelEl, params.xlabel);
			this.setText(this.ylabelEl, params.ylabel);

			if (this.svg) {
				this.svg.setAttribute('aria-label', params.title !== '' ? params.title : 'Диаграмма');
			}

			if (params.points.length === 0) {
				this.clearSvg();
				if (this.emptyEl) {
					this.emptyEl.hidden = false;
				}
				return;
			}

			if (this.emptyEl) {
				this.emptyEl.hidden = true;
			}

			this.draw(params.points);
		}

		clearSvg() {
			if (!this.svg) {
				return;
			}

			while (this.svg.firstChild) {
				this.svg.removeChild(this.svg.firstChild);
			}
		}

		el(name, attrs) {
			const node = document.createElementNS(SVG_NS, name);
			Object.keys(attrs || {}).forEach((key) => {
				node.setAttribute(key, String(attrs[key]));
			});
			return node;
		}

		draw(points) {
			this.clearSvg();
			const maxValue = niceMax(Math.max(0, ...points.map((point) => point.y)));
			const maxLabel = maxValue.toLocaleString('ru-RU', { maximumFractionDigits: 2 });
			const left = Math.max(44, 12 + maxLabel.length * 8);
			const plotWidth = WIDTH - left - RIGHT;
			const plotHeight = HEIGHT - TOP - BOTTOM;
			const slot = plotWidth / points.length;
			const barWidth = Math.max(8, slot * 0.62);

			this.svg.appendChild(this.el('line', {
				x1: left,
				y1: TOP,
				x2: left,
				y2: HEIGHT - BOTTOM,
				stroke: '#e8e2d8',
				'stroke-width': 1
			}));
			this.svg.appendChild(this.el('line', {
				x1: left,
				y1: HEIGHT - BOTTOM,
				x2: WIDTH - RIGHT,
				y2: HEIGHT - BOTTOM,
				stroke: '#e8e2d8',
				'stroke-width': 1
			}));

			for (let tick = 0; tick <= 4; tick += 1) {
				const ratio = tick / 4;
				const y = HEIGHT - BOTTOM - plotHeight * ratio;
				const value = maxValue * ratio;
				this.svg.appendChild(this.el('line', {
					x1: left,
					y1: y,
					x2: WIDTH - RIGHT,
					y2: y,
					stroke: '#e8e2d8',
					'stroke-width': 1
				}));
				const label = this.el('text', {
					x: left - 8,
					y: y + 4,
					'text-anchor': 'end',
					fill: '#666f7b',
					'font-size': 12,
					'font-family': 'Montserrat, sans-serif'
				});
				label.textContent = value.toLocaleString('ru-RU', { maximumFractionDigits: 2 });
				this.svg.appendChild(label);
			}

			points.forEach((point, index) => {
				const height = maxValue > 0 ? (Math.max(0, point.y) / maxValue) * plotHeight : 0;
				const x = left + slot * index + (slot - barWidth) / 2;
				const y = HEIGHT - BOTTOM - height;
				this.svg.appendChild(this.el('rect', {
					x: x,
					y: y,
					width: barWidth,
					height: Math.max(height, point.y === 0 ? 0 : 2),
					rx: 4,
					fill: '#5747e6'
				}));

				const valueLabel = this.el('text', {
					x: x + barWidth / 2,
					y: Math.max(14, y - 6),
					'text-anchor': 'middle',
					fill: '#171717',
					'font-size': 11,
					'font-weight': 600,
					'font-family': 'Montserrat, sans-serif'
				});
				valueLabel.textContent = point.y.toLocaleString('ru-RU', { maximumFractionDigits: 2 });
				this.svg.appendChild(valueLabel);

				const xLabel = this.el('text', {
					x: x + barWidth / 2,
					y: HEIGHT - BOTTOM + 18,
					'text-anchor': 'middle',
					fill: '#666f7b',
					'font-size': points.length > 8 ? 10 : 12,
					'font-family': 'Montserrat, sans-serif'
				});
				xLabel.textContent = point.x;
				this.svg.appendChild(xLabel);
			});
		}
	}

	document.querySelectorAll('[data-widget="bar-chart"]').forEach((root) => {
		new BarChartWidget(root).bind();
	});
})();
