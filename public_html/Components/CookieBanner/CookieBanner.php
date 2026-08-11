<?php

namespace Components\CookieBanner;

use Modules\Main\BaseComponent;
use Modules\Main\Config;

class CookieBanner extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$config = Config::getInstance()->get('App', 'cookies')->toArray();

		$cooldownDays = (int) ($params['cooldown_days'] ?? $config['banner_cooldown_days'] ?? 365);
		if ($cooldownDays < 1) {
			$cooldownDays = 1;
		}

		$policyPath = trim((string) ($params['policy_path'] ?? $config['policy_path'] ?? '/cookies/'));
		if ($policyPath === '') {
			$policyPath = '/cookies/';
		}

		$cookieName = trim((string) ($params['cookie_name'] ?? $config['consent_cookie'] ?? 'ls_cookie_consent'));
		if ($cookieName === '' || !preg_match('/^[a-z0-9_-]+$/i', $cookieName)) {
			$cookieName = 'ls_cookie_consent';
		}

		$storageKey = trim((string) ($params['storage_key'] ?? $config['consent_storage_key'] ?? 'ls_cookie_consent'));
		if ($storageKey === '') {
			$storageKey = 'ls_cookie_consent';
		}

		$existingChoice = strtolower(trim((string) ($_COOKIE[$cookieName] ?? '')));
		$alreadyDecided = in_array($existingChoice, ['accepted', 'declined', '1'], true);

		$this->setParam('cooldown_days', $cooldownDays);
		$this->setParam('policy_path', $policyPath);
		$this->setParam('cookie_name', $cookieName);
		$this->setParam('storage_key', $storageKey);
		$this->setParam('already_decided', $alreadyDecided);
		$this->setParam(
			'text_before',
			(string) ($params['text_before'] ?? 'Мы используем cookie, чтобы сайт работал корректно. Подробнее — в ')
		);
		$this->setParam(
			'policy_link_text',
			(string) ($params['policy_link_text'] ?? 'политике cookie')
		);
		$this->setParam(
			'text_after',
			(string) ($params['text_after'] ?? '.')
		);
		$this->setParam('accept_text', (string) ($params['accept_text'] ?? 'Принять'));
		$this->setParam('decline_text', (string) ($params['decline_text'] ?? 'Отказаться'));
	}

	protected function isEditableInAdmin(): bool
	{
		return false;
	}
}
