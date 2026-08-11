<?php

namespace App\Events\Admin;

use App\Services\Admin\Bar\AdminBarGroup;
use Modules\Main\Event\EventInterface;

/**
 * Формирование центральной зоны публичного AdminBar.
 * Слушатели добавляют группы кнопок (например, «Блог»).
 */
final class AdminBarBuildEvent implements EventInterface
{
	/** @var list<AdminBarGroup> */
	private array $groups = [];

	public function __construct(
		private readonly string $currentPath,
		private readonly bool $isEditMode,
	) {
	}

	public function getCurrentPath(): string
	{
		return $this->currentPath;
	}

	public function isEditMode(): bool
	{
		return $this->isEditMode;
	}

	/** @return list<AdminBarGroup> */
	public function getGroups(): array
	{
		return $this->groups;
	}

	public function addGroup(AdminBarGroup $group, ?string $afterId = null): void
	{
		if ($afterId === null) {
			$this->groups[] = $group;
			return;
		}

		$inserted = false;
		$next = [];

		foreach ($this->groups as $existing) {
			$next[] = $existing;
			if ($existing->getId() === $afterId) {
				$next[] = $group;
				$inserted = true;
			}
		}

		if (!$inserted) {
			$next[] = $group;
		}

		$this->groups = $next;
	}

	public function removeGroup(string $id): bool
	{
		$before = count($this->groups);
		$this->groups = array_values(array_filter(
			$this->groups,
			static fn(AdminBarGroup $group): bool => $group->getId() !== $id
		));

		return count($this->groups) !== $before;
	}

	public function findGroup(string $id): ?AdminBarGroup
	{
		foreach ($this->groups as $group) {
			if ($group->getId() === $id) {
				return $group;
			}
		}

		return null;
	}
}
