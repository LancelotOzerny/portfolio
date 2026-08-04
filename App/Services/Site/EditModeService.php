<?php

namespace App\Services\Site;

use Modules\Main\Auth;

class EditModeService
{
	private const SESSION_KEY = 'site_edit_mode';

	public function handleRequest(): void
	{
		if (!Auth::getInstance()->isAdmin()) {
			$this->setActive(false);
			return;
		}

		if (!array_key_exists('edit', $_GET)) {
			return;
		}

		$value = strtolower(trim((string) $_GET['edit']));

		if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
			$this->setActive(true);
			return;
		}

		if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
			$this->setActive(false);
		}
	}

	public function isActive(): bool
	{
		return Auth::getInstance()->isAdmin() && (bool) ($_SESSION[self::SESSION_KEY] ?? false);
	}

	public function clear(): void
	{
		unset($_SESSION[self::SESSION_KEY]);
	}

	private function setActive(bool $active): void
	{
		if ($active) {
			$_SESSION[self::SESSION_KEY] = true;
			return;
		}

		unset($_SESSION[self::SESSION_KEY]);
	}
}
