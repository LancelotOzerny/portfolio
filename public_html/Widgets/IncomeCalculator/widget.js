(() => {
	const DAYS_IN_YEAR = 365;
	const DAYS_PER_MONTH = DAYS_IN_YEAR / 12;
	const MAX_MONTHS = 360;

	const roundMoney = (value) => Math.round(value * 100) / 100;

	const formatMoney = (value) => value.toLocaleString('ru-RU', {
		minimumFractionDigits: 2,
		maximumFractionDigits: 2
	});

	const parseAmount = (value) => {
		const parsed = Number.parseFloat(String(value || '').replace(',', '.'));
		return Number.isFinite(parsed) ? parsed : NaN;
	};

	class IncomeCalculatorWidget {
		constructor(root) {
			this.root = root;
			this.form = root.querySelector('.widget-income__form');
			this.error = root.querySelector('.widget-income__error');
			this.result = root.querySelector('.widget-income__result');
			this.body = root.querySelector('.widget-income__table tbody');
			this.foot = root.querySelector('.widget-income__table tfoot');
			this.total = root.querySelector('.widget-income__total');
		}

		bind() {
			if (!this.form || this.root.dataset.widgetReady === '1') {
				return;
			}

			this.root.dataset.widgetReady = '1';
			this.form.addEventListener('submit', (event) => {
				event.preventDefault();
				this.calculate();
			});
		}

		calculate() {
			const input = this.readInput();
			if (input === null) {
				return;
			}

			const rows = this.buildRows(input);
			this.render(rows);
		}

		readInput() {
			const formData = new FormData(this.form);
			const initial = parseAmount(formData.get('initial'));
			const monthly = parseAmount(formData.get('monthly'));
			const percent = parseAmount(formData.get('percent'));
			const months = Number.parseInt(String(formData.get('months') || ''), 10);
			const compound = formData.get('compound') === 'daily' ? 'daily' : 'monthly';

			if (![initial, monthly, percent].every((value) => Number.isFinite(value) && value >= 0) || !Number.isInteger(months) || months < 1) {
				this.showError('Проверьте введённые значения.');
				return null;
			}

			if (months > MAX_MONTHS) {
				this.showError('Максимум 360 месяцев.');
				return null;
			}

			this.showError('');
			return { initial, monthly, percent, months, compound };
		}

		buildRows({ initial, monthly, percent, months, compound }) {
			const rows = [];
			let balance = roundMoney(initial);

			for (let month = 1; month <= months; month++) {
				const start = balance;
				const interest = roundMoney(this.interestForMonth(start, percent, compound));
				const total = roundMoney(start + interest + monthly);
				rows.push({ month, start, interest, monthly, total });
				balance = total;
			}

			return rows;
		}

		interestForMonth(start, percent, compound) {
			const rate = percent / 100;
			if (rate === 0 || start === 0) {
				return 0;
			}

			if (compound === 'daily') {
				return start * (Math.pow(1 + rate / DAYS_IN_YEAR, DAYS_PER_MONTH) - 1);
			}

			return start * (rate / 12);
		}

		render(rows) {
			if (!this.body || !this.foot || !this.result || !this.total) {
				return;
			}

			const interestSum = roundMoney(rows.reduce((sum, row) => sum + row.interest, 0));
			const monthlySum = roundMoney(rows.reduce((sum, row) => sum + row.monthly, 0));
			const finalAmount = rows.length > 0 ? rows[rows.length - 1].total : 0;

			this.body.replaceChildren(...rows.map((row) => this.createRow([
				String(row.month),
				formatMoney(row.start),
				formatMoney(row.interest),
				formatMoney(row.monthly),
				formatMoney(row.total)
			])));

			this.foot.replaceChildren(this.createRow([
				'Итого',
				'',
				formatMoney(interestSum),
				formatMoney(monthlySum),
				formatMoney(finalAmount)
			]));

			this.total.textContent = 'Итого: ' + formatMoney(finalAmount);
			this.result.hidden = false;
		}

		createRow(cells) {
			const tr = document.createElement('tr');
			cells.forEach((value) => {
				const cell = document.createElement('td');
				cell.textContent = value;
				tr.appendChild(cell);
			});
			return tr;
		}

		showError(message) {
			if (!this.error) {
				return;
			}

			this.error.textContent = message;
			this.error.hidden = message === '';
		}
	}

	document.querySelectorAll('[data-widget="income-calculator"]').forEach((root) => {
		new IncomeCalculatorWidget(root).bind();
	});
})();
