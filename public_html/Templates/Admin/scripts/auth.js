document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('auth-form');
    if (!form) {
        return;
    }

    const alertBox = document.getElementById('auth-alert');
    const submitButton = document.getElementById('auth-submit');

    const showError = (message) => {
        if (!alertBox) {
            return;
        }

        alertBox.textContent = message || 'Ошибка авторизации';
        alertBox.classList.remove('d-none');
    };

    const hideError = () => {
        if (!alertBox) {
            return;
        }

        alertBox.textContent = '';
        alertBox.classList.add('d-none');
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        hideError();

        const login = document.getElementById('login')?.value?.trim() || '';
        const password = document.getElementById('password')?.value || '';

        if (!login || !password) {
            showError('Введите логин и пароль');
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/auth/login/');
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onload = () => {
            if (submitButton) {
                submitButton.disabled = false;
            }

            let response = {};
            try {
                response = JSON.parse(xhr.responseText || '{}');
            } catch (e) {
                showError('Некорректный ответ сервера');
                return;
            }

            if (xhr.status >= 200 && xhr.status < 300 && response.success) {
                window.location.href = response.redirect || '/admin/';
                return;
            }

            if (response.errors) {
                const firstError = Object.values(response.errors)[0];
                showError(typeof firstError === 'string' ? firstError : 'Ошибка валидации');
                return;
            }

            showError(response.message || 'Ошибка авторизации');
        };

        xhr.onerror = () => {
            if (submitButton) {
                submitButton.disabled = false;
            }

            showError('Сетевая ошибка');
        };

        xhr.send(JSON.stringify({
            login: login,
            password: password
        }));
    });
});
