<?php

use App\Events\Admin\AdminBarBuildEvent;
use App\Events\Admin\AdminMenuBuildEvent;
use App\Listeners\Admin\BlogAdminBarListener;
use App\Listeners\Admin\DefaultAdminMenuListener;
use Modules\Main\Event\EventDispatcher;

/**
 * Регистрация слушателей событий сайта.
 * Подключать новые listen() здесь.
 */
$dispatcher = EventDispatcher::getInstance();

$dispatcher->listen(
	AdminMenuBuildEvent::class,
	new DefaultAdminMenuListener(),
	100
);

$dispatcher->listen(
	AdminBarBuildEvent::class,
	new BlogAdminBarListener(),
	0
);
