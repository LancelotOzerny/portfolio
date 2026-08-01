<?php

namespace Components\ContactForm;

use Modules\Main\BaseComponent;

class ContactForm extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$recipient = trim((string) ($params['recipient'] ?? ''));
		$theme = trim((string) ($params['theme'] ?? 'Новое сообщение с сайта'));

		$this->setParam('recipient', $recipient);
		$this->setParam('theme', $theme);
		$this->setParam('form_hash', $this->getFormHash($recipient, $theme));
	}

	public static function getFormHash(string $recipient, string $theme): string
	{
		return hash_hmac('sha256', $recipient . '|' . $theme, self::class);
	}
}
