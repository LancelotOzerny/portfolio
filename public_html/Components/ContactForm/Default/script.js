document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-contact-form]').forEach((form) => {
		form.addEventListener('submit', async (event) => {
			event.preventDefault();

			const status = form.querySelector('[data-contact-form-status]');
			const submit = form.querySelector('[type="submit"]');
			const formData = new FormData(form);

			if (status) {
				status.textContent = 'Отправляю...';
			}

			if (submit) {
				submit.disabled = true;
			}

			try {
				const response = await fetch('/api/feedback/send/', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
					},
					body: JSON.stringify(Object.fromEntries(formData.entries())),
				});

				const result = await response.json();
				if (status) {
					status.textContent = result.message || 'Сообщение отправлено';
				}

				if (result.success) {
					form.reset();
				}
			} catch (error) {
				if (status) {
					status.textContent = 'Не удалось отправить сообщение';
				}
			} finally {
				if (submit) {
					submit.disabled = false;
				}
			}
		});
	});
});
