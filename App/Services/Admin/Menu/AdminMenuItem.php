<?php

namespace App\Services\Admin\Menu;

final class AdminMenuItem
{
	/** @var list<self> */
	private array $children;

	/**
	 * @param list<string> $matchPrefixes Префиксы URI для active
	 * @param list<string> $matchExact Точные URI для active
	 * @param list<self> $children
	 */
	public function __construct(
		private string $id,
		private string $label,
		private string $href,
		private string $icon = '',
		private array $matchPrefixes = [],
		private array $matchExact = [],
		array $children = [],
	) {
		$this->children = $children;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getHref(): string
	{
		return $this->href;
	}

	public function getIcon(): string
	{
		return $this->icon;
	}

	/** @return list<self> */
	public function getChildren(): array
	{
		return $this->children;
	}

	public function hasChildren(): bool
	{
		return $this->children !== [];
	}

	public function addChild(self $child, ?string $afterId = null): void
	{
		if ($afterId === null) {
			$this->children[] = $child;
			return;
		}

		$inserted = false;
		$next = [];

		foreach ($this->children as $existing) {
			$next[] = $existing;
			if ($existing->getId() === $afterId) {
				$next[] = $child;
				$inserted = true;
			}
		}

		if (!$inserted) {
			$next[] = $child;
		}

		$this->children = $next;
	}

	public function removeChild(string $id): bool
	{
		$before = count($this->children);
		$this->children = array_values(array_filter(
			$this->children,
			static fn(self $item): bool => $item->getId() !== $id
		));

		if (count($this->children) !== $before) {
			return true;
		}

		foreach ($this->children as $child) {
			if ($child->removeChild($id)) {
				return true;
			}
		}

		return false;
	}

	public function find(string $id): ?self
	{
		if ($this->id === $id) {
			return $this;
		}

		foreach ($this->children as $child) {
			$found = $child->find($id);
			if ($found !== null) {
				return $found;
			}
		}

		return null;
	}

	public function isActive(string $currentPath): bool
	{
		foreach ($this->matchExact as $exact) {
			if ($currentPath === $exact) {
				return true;
			}
		}

		foreach ($this->matchPrefixes as $prefix) {
			if ($prefix !== '' && str_starts_with($currentPath, rtrim($prefix, '/') . '/')) {
				return true;
			}
			if ($prefix !== '' && $currentPath === rtrim($prefix, '/')) {
				return true;
			}
		}

		foreach ($this->children as $child) {
			if ($child->isActive($currentPath)) {
				return true;
			}
		}

		return false;
	}
}
