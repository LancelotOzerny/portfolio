# Система событий

Лёгкий Event Dispatcher без сторонних библиотек.  
События идентифицируются по FQCN класса (как в PSR-14).

## Структура

| Путь | Назначение |
|------|------------|
| `Core/Modules/Main/Event/EventDispatcher.php` | Диспетчер: `listen`, `dispatch`, `forget` |
| `Core/Modules/Main/Event/EventInterface.php` | Маркер события |
| `App/Events/` | Классы событий |
| `App/Events/listeners.php` | Регистрация слушателей (подключается в `App::init`) |
| `App/Listeners/` | Слушатели |

## Как подписаться на событие

В `App/Events/listeners.php`:

```php
use App\Events\Admin\AdminMenuBuildEvent;
use Modules\Main\Event\EventDispatcher;

$dispatcher = EventDispatcher::getInstance();

$dispatcher->listen(
    AdminMenuBuildEvent::class,
    function (AdminMenuBuildEvent $event): void {
        // логика
    },
    0 // приоритет: больше — раньше
);
```

Слушатель может быть:

- замыканием (`function (...) {}`);
- объектом с `__invoke`;
- статическим методом `[SomeClass::class, 'method']`.

Приоритет:

- больше значение — раньше выполнение;
- базовое админ-меню: приоритет `100`;
- расширения меню обычно: `0` или ниже.

## Как вызвать (dispatch) событие

```php
use App\Events\Admin\AdminMenuBuildEvent;
use Modules\Main\Event\EventDispatcher;

$event = new AdminMenuBuildEvent($currentPath);
EventDispatcher::getInstance()->dispatch($event);
```

Слушатели получают тот же объект события и могут менять его состояние.

## Как отписаться

```php
$dispatcher = EventDispatcher::getInstance();

// один слушатель
$dispatcher->forget(AdminMenuBuildEvent::class, $listener);

// все слушатели события
$dispatcher->forget(AdminMenuBuildEvent::class);
```

`$listener` должен быть тем же callable, что передавали в `listen`.

## Как добавить новое событие

1. Создать класс в `App/Events/...`, реализующий `Modules\Main\Event\EventInterface`.
2. В нужном месте кода создать экземпляр и вызвать `EventDispatcher::dispatch($event)`.
3. Зарегистрировать слушателей в `App/Events/listeners.php`.
4. Описать событие в этом файле (раздел «Текущие события»).

Пример каркаса:

```php
namespace App\Events;

use Modules\Main\Event\EventInterface;

final class ExampleEvent implements EventInterface
{
    public function __construct(
        public readonly string $something,
    ) {
    }
}
```

## Текущие события

### `App\Events\Admin\AdminMenuBuildEvent`

Формирование бокового меню админки.

**Когда вызывается:** при рендере `public_html/Templates/Admin/header.php` (через `AdminMenuService`).

**Данные:**

- `getCurrentPath(): string` — текущий URI path;
- `getItems(): AdminMenuItem[]` — пункты меню;
- `addItem(AdminMenuItem $item, ?string $afterId = null)` — добавить пункт верхнего уровня;
- `addChild(string $parentId, AdminMenuItem $child, ?string $afterId = null)` — добавить вложенный пункт;
- `removeItem(string $id)` — удалить пункт (на любом уровне);
- `findItem(string $id): ?AdminMenuItem` — найти пункт по id.

**Базовый слушатель:** `App\Listeners\Admin\DefaultAdminMenuListener` (приоритет `100`).

**Id пунктов по умолчанию:**

- `home`
- `content` → `projects` → `projects.list`, `projects.tags`
- `content` → `blog` → `blog.rubrics`, `blog.articles`, `blog.comments`
- `content` → `gallery`
- `seo`
- `users`
- `resume` → `resume.experience`
- `development` → `development.sql`, `development.todo`, `development.repository`
- `settings` → `settings.configs`, `settings.templates`, `settings.backup` → `settings.backup.create`, `settings.backup.list`

### Пример: добавить пункт в меню

```php
use App\Events\Admin\AdminMenuBuildEvent;
use App\Services\Admin\Menu\AdminMenuItem;
use Modules\Main\Event\EventDispatcher;

EventDispatcher::getInstance()->listen(
    AdminMenuBuildEvent::class,
    function (AdminMenuBuildEvent $event): void {
        $event->addItem(
            new AdminMenuItem(
                id: 'reports',
                label: 'Отчёты',
                href: '/admin/reports/',
                icon: 'От',
                matchPrefixes: ['/admin/reports/'],
            ),
            afterId: 'seo'
        );
    }
);
```

### Пример: добавить вкладку-ссылку внутрь «Блог»

```php
EventDispatcher::getInstance()->listen(
    AdminMenuBuildEvent::class,
    function (AdminMenuBuildEvent $event): void {
        $event->addChild(
            'blog',
            new AdminMenuItem(
                id: 'blog.seo',
                label: 'SEO блога',
                href: '/admin/content/blog/seo/',
                matchPrefixes: ['/admin/content/blog/seo/'],
            ),
            afterId: 'blog.comments'
        );
    }
);
```

### Пример: убрать пункт

```php
EventDispatcher::getInstance()->listen(
    AdminMenuBuildEvent::class,
    function (AdminMenuBuildEvent $event): void {
        $event->removeItem('users');
    }
);
```

## Замечания

- Пока реализовано только событие админ-меню; диспетчер готов к новым событиям без смены архитектуры.
- Не меняйте разметку меню напрямую в `header.php` — расширяйте через `AdminMenuBuildEvent`.
