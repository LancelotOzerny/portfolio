<?php

namespace Controllers\Api;

use App\Services\Component\ComponentSettingsStorage;
use Modules\Main\Auth;

class ComponentController
{
	public function saveSettings(): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!Auth::getInstance()->isAdmin()) {
			$this->respond(false, 'Недостаточно прав.');
			return;
		}

		$data = json_decode((string) file_get_contents('php://input'), true);
		if (!is_array($data)) {
			$this->respond(false, 'Некорректный запрос.');
			return;
		}

		$key = trim((string) ($data['key'] ?? ''));
		$params = $data['params'] ?? null;

		if ($key === '' || !is_array($params)) {
			$this->respond(false, 'Некорректные данные компонента.');
			return;
		}

		$normalized = [];
		foreach ($params as $name => $value) {
			if (!is_string($name) || $name === '') {
				continue;
			}

			if (is_bool($value) || is_int($value) || is_float($value) || is_string($value) || $value === null) {
				$normalized[$name] = $value;
			}
		}

		try {
			ComponentSettingsStorage::set($key, $normalized);
		} catch (\Throwable $exception) {
			$this->respond(false, $exception->getMessage());
			return;
		}

		$this->respond(true, 'Сохранено.');
	}

	private function respond(bool $success, string $message): void
	{
		echo json_encode([
			'success' => $success,
			'message' => $message,
		], JSON_UNESCAPED_UNICODE);
	}
}
