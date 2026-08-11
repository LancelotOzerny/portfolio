<?php

namespace App\Events\Admin;

use App\Services\Admin\Menu\AdminMenuItem;
use Modules\Main\Event\EventInterface;

/**
 * Формирование бокового меню админки.
 * Слушатели могут добавлять, удалять и изменять пункты.
 */
final class AdminMenuBuildEvent implements EventInterface
{
	/** @var list<AdminMenuItem> */
	private array $items = [];

	public function __construct(
		private readonly string $currentPath,
	) {
	}

	public function getCurrentPath(): string
	{
		return $this->currentPath;
	}

	/** @return list<AdminMenuItem> */
	public function getItems(): array
	{
		return $this->items;
	}

	public function addItem(AdminMenuItem $item, ?string $afterId = null): void
	{
		if ($afterId === null) {
			$this->items[] = $item;
			return;
		}

		$inserted = false;
		$next = [];

		foreach ($this->items as $existing) {
			$next[] = $existing;
			if ($existing->getId() === $afterId) {
				$next[] = $item;
				$inserted = true;
			}
		}

		if (!$inserted) {
			$next[] = $item;
		}

		$this->items = $next;
	}

	public function removeItem(string $id): bool
	{
		$before = count($this->items);
		$this->items = array_values(array_filter(
			$this->items,
			static fn(AdminMenuItem $item): bool => $item->getId() !== $id
		));

		if (count($this->items) !== $before) {
			return true;
		}

		foreach ($this->items as $item) {
			if ($item->removeChild($id)) {
				return true;
			}
		}

		return false;
	}

	public function findItem(string $id): ?AdminMenuItem
	{
		foreach ($this->items as $item) {
			$found = $item->find($id);
			if ($found !== null) {
				return $found;
			}
		}

		return null;
	}

	/**
	 * Добавляет дочерний пункт к существующему родителю.
	 */
	public function addChild(string $parentId, AdminMenuItem $child, ?string $afterId = null): bool
	{
		$parent = $this->findItem($parentId);
		if ($parent === null) {
			return false;
		}

		$parent->addChild($child, $afterId);
		return true;
	}
}
