<?php

namespace App\Events\Page;

use Modules\Main\Event\EventInterface;

/**
 * Начало формирования страницы (после роутинга, до вызова контроллера).
 */
final class PageBuildStartEvent implements EventInterface
{
	/**
	 * @param class-string $controllerClass
	 * @param array<string, mixed> $params
	 */
	public function __construct(
		private readonly string $method,
		private readonly string $path,
		private readonly string $controllerClass,
		private readonly string $action,
		private readonly array $params,
	) {
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * @return class-string
	 */
	public function getControllerClass(): string
	{
		return $this->controllerClass;
	}

	public function getAction(): string
	{
		return $this->action;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getParams(): array
	{
		return $this->params;
	}
}
