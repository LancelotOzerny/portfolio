<?php

namespace Controllers\Api;

use Components\ContactForm\ContactForm;

class FeedbackController
{
	public function send() : void
	{
		$result = [
			'success' => false,
			'message' => 'Неизвестная ошибка'
		];

		$json = file_get_contents('php://input');
		$data = json_decode($json, true);
		if (!is_array($data)) {
			$data = [];
		}

		$name = trim((string) ($data['name'] ?? ''));
		$email = trim((string) ($data['email'] ?? ''));
		$message = trim((string) ($data['message'] ?? ''));
		$recipient = trim((string) ($data['recipient'] ?? ''));
		$theme = trim((string) ($data['theme'] ?? ''));
		$formHash = (string) ($data['form_hash'] ?? '');

		if (empty($name) || empty($email) || empty($message))
		{
			$result['message'] = 'Заполните все поля формы';
			echo json_encode($result);
			return;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$result['message'] = 'Некорректный email';
			echo json_encode($result);
			return;
		}

		$to = 'lancelot.ozernuy@gmail.com';
		$subject = 'Новое сообщение с сайта';
		if (
			$recipient !== ''
			&& filter_var($recipient, FILTER_VALIDATE_EMAIL)
			&& $theme !== ''
			&& hash_equals(ContactForm::getFormHash($recipient, $theme), $formHash)
		) {
			$to = $recipient;
			$subject = $theme;
		}

		$body = "Получено новое сообщение:\n\n";
		$body .= "От кого: $name\n";
		$body .= "Email: $email\n";
		$body .= "Сообщение:\n$message\n";

		$headers = [
			'From: ' . $email,
			'Reply-To: ' . $email,
			'Content-Type: text/plain; charset=utf-8'
		];

		$mailSent = mail($to, $subject, $body, implode("\r\n", $headers));

		if ($mailSent ?? null)
		{
			$result['success'] = true;
			$result['message'] = 'Сообщение успешно отправлено!';
		}
		else
		{
			$result['message'] = 'Ошибка при отправке письма';
		}

		echo json_encode($result);
	}
}
