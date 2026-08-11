<?php

namespace App\Services\Admin\Bar;

final class AdminBarAction
{
	/**
	 * @param array<string, string> $attributes HTML-атрибуты кнопки/ссылки
	 */
	public function __construct(
		private string $id,
		private string $label,
		private string $type = 'button',
		private string $href = '',
		private array $attributes = [],
	) {
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getHref(): string
	{
		return $this->href;
	}

	/** @return array<string, string> */
	public function getAttributes(): array
	{
		return $this->attributes;
	}
}
