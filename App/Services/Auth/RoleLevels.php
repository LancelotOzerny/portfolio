<?php

namespace App\Services\Auth;

use Models\RightsModel;
use Throwable;

final class RoleLevels
{
	/** @var array<string, int> */
	private array $levelsByCode = [];

	public function __construct(?RightsModel $rightsModel = null)
	{
		$this->load($rightsModel ?? new RightsModel());
	}

	public function getLevel(string $code): int
	{
		$code = strtolower(trim($code));
		if ($code === '') {
			return 0;
		}

		return $this->levelsByCode[$code] ?? 0;
	}

	public function isAtLeast(string $userCode, string $requiredCode): bool
	{
		$userCode = strtolower(trim($userCode));
		$requiredCode = strtolower(trim($requiredCode));
		if ($userCode === '' || $requiredCode === '') {
			return false;
		}

		if ($userCode === $requiredCode) {
			return true;
		}

		$requiredLevel = $this->getLevel($requiredCode);
		if ($requiredLevel <= 0) {
			return false;
		}

		return $this->getLevel($userCode) >= $requiredLevel;
	}

	private function load(RightsModel $rightsModel): void
	{
		try {
			$roles = $rightsModel->findAllOrdered();
		} catch (Throwable) {
			return;
		}

		foreach ($roles as $role) {
			$code = strtolower(trim((string) ($role->code ?? '')));
			if ($code === '') {
				continue;
			}

			$this->levelsByCode[$code] = (int) ($role->level ?? 0);
		}
	}
}
