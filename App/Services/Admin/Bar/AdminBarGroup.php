<?php

namespace App\Services\Admin\Bar;

final class AdminBarGroup
{
	/** @var list<AdminBarAction> */
	private array $actions;

	/**
	 * @param list<AdminBarAction> $actions
	 */
	public function __construct(
		private string $id,
		private string $label,
		array $actions = [],
	) {
		$this->actions = $actions;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	/** @return list<AdminBarAction> */
	public function getActions(): array
	{
		return $this->actions;
	}

	public function addAction(AdminBarAction $action): void
	{
		$this->actions[] = $action;
	}
}
