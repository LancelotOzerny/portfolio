document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-cookie-banner]').forEach((banner) => {
		if (banner.hidden) {
			return;
		}

		const cookieName = String(banner.getAttribute('data-cookie-name') || 'ls_cookie_consent').trim();
		const storageKey = String(banner.getAttribute('data-storage-key') || cookieName).trim();
		const cooldownDays = Math.max(1, Number(banner.getAttribute('data-cooldown-days') || 365));
		const acceptButton = banner.querySelector('[data-cookie-banner-accept]');
		const declineButton = banner.querySelector('[data-cookie-banner-decline]');

		const getCookie = (name) => {
			const prefix = name + '=';
			const parts = document.cookie.split(';');

			for (let i = 0; i < parts.length; i += 1) {
				const part = parts[i].trim();
				if (part.indexOf(prefix) === 0) {
					return decodeURIComponent(part.slice(prefix.length));
				}
			}

			return '';
		};

		const setCookie = (name, value, days) => {
			const maxAge = Math.floor(days * 24 * 60 * 60);
			const secure = window.location.protocol === 'https:' ? '; Secure' : '';
			document.cookie = name + '=' + encodeURIComponent(value)
				+ '; Path=/'
				+ '; Max-Age=' + maxAge
				+ '; SameSite=Lax'
				+ secure;
		};

		const saveChoice = (value) => {
			setCookie(cookieName, value, cooldownDays);

			try {
				window.localStorage.setItem(storageKey, value);
			} catch (error) {}

			banner.hidden = true;
		};

		const existingCookie = getCookie(cookieName);
		let existingStorage = '';
		try {
			existingStorage = window.localStorage.getItem(storageKey) || '';
		} catch (error) {}

		if (['accepted', 'declined', '1'].includes(existingCookie) || ['accepted', 'declined', '1'].includes(existingStorage)) {
			banner.hidden = true;
			return;
		}

		if (acceptButton) {
			acceptButton.addEventListener('click', () => saveChoice('accepted'));
		}

		if (declineButton) {
			declineButton.addEventListener('click', () => saveChoice('declined'));
		}
	});
});
