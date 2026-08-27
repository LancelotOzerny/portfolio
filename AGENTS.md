# Правила для ИИ-агента

Документ описывает сайт **Lancy Studio** (`lancy.test` / `lancy-dev.ru`) и то, как в нём писать код.

## 1. Контекст

Персональный сайт-портфолио с блогом, админкой и JSON API. Свой MVC, без Laravel/Symfony/Composer.

**Корень:** `/var/www/lancy-studio`  
**DocumentRoot Apache:** `public_html/`  
**Точка входа:** `public_html/index.php` → `Core/bootstrap.php`

| Путь | Назначение |
|------|------------|
| `Core/` | Ядро: роутер, шаблоны, PDO, автозагрузка, события |
| `App/Controllers/` | Контроллеры `Public`, `Admin`, API |
| `App/Models/` | Модели БД |
| `App/Services/` | Бизнес-логика |
| `App/Views/` | PHP-шаблоны страниц |
| `App/Routes/` | `web.php`, `admin.php`, `api.php` |
| `App/Configs/` | Конфиги (`Hidden/` — секреты, не трогать без нужды) |
| `public_html/Templates/` | Обвязка сайта: `Light` (публичка), `Admin` |
| `public_html/Components/` | Компоненты с вариантами шаблонов (`Default`, `Light`, …) |
| `public_html/Widgets/` | Виджеты редактора блога (`widget.json` + `html/css/js`) |
| `public_html/assets/` | Общие CSS/JS, в т.ч. Editor.js |

Публичные разделы: главная, о себе, портфолио, блог, сертификаты, контакты.  
Блог: рубрики, статьи, комментарии, рейтинг, SEO, виджеты в тексте (Editor.js).  
Админка: пользователи, роли, проекты, блог, SEO, галерея, бэкапы, cron, настройки.

Новый код класть в существующие слои: маршрут → контроллер → сервис/модель → view или компонент. Виджет статьи — в `public_html/Widgets/{Name}/`, он подхватывается каталогом автоматически.

## 2. Правила и ограничения разработки

- Работать как профессиональный PHP-разработчик.
- Писать в ООП-стиле, соблюдать SOLID.
- Писать минимально нужный код, без спагетти и без «на будущее».
- Не подключать библиотеки и фреймворки без явного разрешения.
- Не менять архитектуру сайта без необходимости.
- Не ломать текущие соглашения: неймспейсы, `BaseController` / `BaseModel` / `BaseComponent`, PSR-подобный автолоад.
- Пользовательский HTML (статьи, комментарии) пропускать через существующие санитайзеры; формы — CSRF.
- Строки интерфейса — на русском.
- Секреты (`App/Configs/Hidden/`, пароли, токены) не выводить и не коммитить.
- UI-изменения проверять в браузере на затронутых страницах.

## 3. Стек технологий

- **PHP 8+**, ООП, типизация, `readonly`.
- **Свой MVC:** `Router`, `App`, `Template`, `AssetLoader`, Event Dispatcher.
- **MySQL** через **PDO** (`Modules\DBWork\DBConnection`).
- **Apache 2**, `ServerName lancy.test`, `AllowOverride All`.
- **Шаблоны:** PHP + CSS; публичка — `Light` (CSS-переменные `--ml-*`, шрифт Montserrat); админка — Bootstrap 5.3.
- **JS:** ванильный в виджетах и публичке; jQuery и Bootstrap JS — в шаблонах Default/Admin/Inner.
- **Редактор статей:** Editor.js (`@editorjs/*` с CDN) + `public_html/assets/js/content-editor.js`.
- **Виджеты блога:** JSON-поля `number` / `select` / `text` / `rows`, рендер на фронте без сторонних чартов.
- **API блога:** JSON, Bearer-токен для ролей `admin` / `ai-agent` (`Docs/blog-api.md`).
- Сборки npm/composer в проекте нет — зависимости в репозиторий не добавлять.