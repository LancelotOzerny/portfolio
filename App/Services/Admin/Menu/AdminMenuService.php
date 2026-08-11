<?php

namespace App\Services\Admin\Menu;

use App\Events\Admin\AdminMenuBuildEvent;
use Modules\Main\Event\EventDispatcher;

final class AdminMenuService
{
	public function build(string $currentPath): AdminMenuBuildEvent
	{
		$event = new AdminMenuBuildEvent($currentPath);
		EventDispatcher::getInstance()->dispatch($event);

		return $event;
	}

	public function renderNav(string $currentPath): string
	{
		$event = $this->build($currentPath);

		return (new AdminMenuRenderer())->render($event->getItems(), $currentPath);
	}
}
