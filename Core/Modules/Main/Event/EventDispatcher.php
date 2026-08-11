<?php

namespace Modules\Main\Event;

/**
 * Простой диспетчер событий без сторонних библиотек.
 * Подписка по FQCN класса события, с приоритетом (больше — раньше).
 */
class EventDispatcher
{
	private static ?self $instance = null;

	/** @var array<string, list<array{0: callable, 1: int}>> */
	private array $listeners = [];

	private function __construct()
	{
	}

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param class-string<EventInterface> $eventClass
	 */
	public function listen(string $eventClass, callable $listener, int $priority = 0): void
	{
		$this->listeners[$eventClass][] = [$listener, $priority];
		usort(
			$this->listeners[$eventClass],
			static fn(array $a, array $b): int => $b[1] <=> $a[1]
		);
	}

	public function dispatch(EventInterface $event): EventInterface
	{
		$eventClass = $event::class;

		foreach ($this->listeners[$eventClass] ?? [] as [$listener]) {
			$listener($event);
		}

		return $event;
	}

	/**
	 * Удаляет слушателя. Без $listener — всех слушателей события.
	 *
	 * @param class-string<EventInterface> $eventClass
	 */
	public function forget(string $eventClass, ?callable $listener = null): void
	{
		if (!isset($this->listeners[$eventClass])) {
			return;
		}

		if ($listener === null) {
			unset($this->listeners[$eventClass]);
			return;
		}

		$this->listeners[$eventClass] = array_values(array_filter(
			$this->listeners[$eventClass],
			static fn(array $entry): bool => $entry[0] !== $listener
		));

		if ($this->listeners[$eventClass] === []) {
			unset($this->listeners[$eventClass]);
		}
	}

	/**
	 * @param class-string<EventInterface> $eventClass
	 */
	public function hasListeners(string $eventClass): bool
	{
		return ($this->listeners[$eventClass] ?? []) !== [];
	}
}
