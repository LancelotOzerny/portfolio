<?php

namespace App\Services\Site;

use Modules\Main\Auth;

class EditModeService
{
	private const SESSION_KEY = 'site_edit_mode';
	private const OPTIMIZATION_SESSION_KEY = 'site_optimization_mode';

	public function handleRequest(): void
	{
		if (!Auth::getInstance()->isAdmin()) {
			$this->clear();
			return;
		}

		if (array_key_exists('optimization', $_GET)) {
			$optimizationActive = $this->parseBoolean($_GET['optimization']);
			if ($optimizationActive !== null) {
				$this->setOptimizationActive($optimizationActive);
			}
		}

		if (array_key_exists('edit', $_GET)) {
			$editActive = $this->parseBoolean($_GET['edit']);
			if ($editActive !== null) {
				$this->setActive($editActive);
			}
		}
	}

	public function isActive(): bool
	{
		return Auth::getInstance()->isAdmin() && (bool) ($_SESSION[self::SESSION_KEY] ?? false);
	}

	public function isOptimizationActive(): bool
	{
		return Auth::getInstance()->isAdmin()
			&& $this->isActive()
			&& (bool) ($_SESSION[self::OPTIMIZATION_SESSION_KEY] ?? false);
	}

	public function clear(): void
	{
		unset($_SESSION[self::SESSION_KEY], $_SESSION[self::OPTIMIZATION_SESSION_KEY]);
	}

	private function setActive(bool $active): void
	{
		if ($active) {
			$_SESSION[self::SESSION_KEY] = true;
			return;
		}

		$this->clear();
	}

	private function setOptimizationActive(bool $active): void
	{
		if ($active) {
			$_SESSION[self::OPTIMIZATION_SESSION_KEY] = true;
			$_SESSION[self::SESSION_KEY] = true;
			return;
		}

		unset($_SESSION[self::OPTIMIZATION_SESSION_KEY]);
	}

	private function parseBoolean(mixed $value): ?bool
	{
		$value = strtolower(trim((string) $value));

		if (in_array($value, ['1', 'true', 'yes', 'on'], true)) {
			return true;
		}

		if (in_array($value, ['0', 'false', 'no', 'off'], true)) {
			return false;
		}

		return null;
	}
}
