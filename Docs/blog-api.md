# API блога

JSON API сайта [lancy.dev.ru](https://lancy.dev.ru) для рубрик, статей и загрузки изображений.

Базовый адрес: `https://lancy.dev.ru`  
Формат ответа: `application/json; charset=utf-8`  
Все пути заканчиваются `/`.

## Содержание

1. [Авторизация](#авторизация)
2. [Статусы материалов](#статусы-материалов)
3. [Рубрики](#рубрики)
4. [Список статей](#список-статей)
5. [Статья](#статья)
6. [Создание статьи](#создание-статьи)
7. [Редактирование SEO](#редактирование-seo)
8. [Редактирование preview-текста](#редактирование-preview-текста)
9. [Редактирование preview-изображения](#редактирование-preview-изображения)
10. [Редактирование detail-изображения](#редактирование-detail-изображения)
11. [Загрузка изображения](#загрузка-изображения)
12. [Коды ответов](#коды-ответов)

## Авторизация

Чтение опубликованных рубрик и статей доступно без токена.

Черновики, создание статей, редактирование и загрузка изображений доступны ролям `ai-agent` и `admin`. Токен передаётся в заголовке:

```http
Authorization: Bearer <token>
```

Токен выпускается в админке: **Пользователи → карточка пользователя**.

Текстовые методы создания и редактирования принимают JSON (`application/json`) или `application/x-www-form-urlencoded`. Загрузка файлов — только `multipart/form-data`.

## Статусы материалов

| Значение     | Смысл                                      |
|--------------|--------------------------------------------|
| `published`  | материал опубликован                       |
| `draft`      | черновик или ещё не опубликованная статья  |

Без токена в ответах только `published`. С токеном `ai-agent` / `admin` возвращаются и черновики.

## Рубрики

Возвращает список рубрик блога.

```http
GET /api/blog/rubrics/
```

### Вызов

```bash
curl -X GET "https://lancy.dev.ru/api/blog/rubrics/"
```

С черновиками:

```bash
curl -X GET "https://lancy.dev.ru/api/blog/rubrics/" \
  -H "Authorization: Bearer <token>"
```

### Ответ

```json
{
  "success": true,
  "items": [
    {
      "id": 3,
      "created_at": "2026-04-12 18:40:01",
      "title": "Программирование",
      "code": "programming",
      "detail_text": "Заметки о разработке.",
      "preview_text": "Код, архитектура и инструменты.",
      "status": "published",
      "seo": {
        "title": "Программирование — блог",
        "description": "Статьи о PHP и веб-разработке.",
        "keywords": "php, backend",
        "robots_index": true,
        "robots_follow": true
      }
    }
  ]
}
```

## Список статей

Возвращает до 100 последних статей: сначала новые.

```http
GET /api/blog/articles/
GET /api/blog/articles/?rubric=<id или символьный код>
```

Параметр `rubric` необязательный. Без него выводятся последние 100 статей сайта. С параметром — последние 100 статей указанной рубрики. Рубрику можно задать числовым `id` или символьным кодом.

### Вызов

Последние статьи:

```bash
curl -X GET "https://lancy.dev.ru/api/blog/articles/"
```

Статьи рубрики по коду:

```bash
curl -X GET "https://lancy.dev.ru/api/blog/articles/?rubric=programming"
```

Статьи рубрики по id, включая черновики:

```bash
curl -X GET "https://lancy.dev.ru/api/blog/articles/?rubric=3" \
  -H "Authorization: Bearer <token>"
```

### Ответ

```json
{
  "success": true,
  "items": [
    {
      "id": 12,
      "title": "Как устроен этот сайт",
      "preview_text": "Кратко о стеке и структуре проекта.",
      "code": "12",
      "published_at": "2026-08-10 14:20:00",
      "status": "published"
    }
  ]
}
```

Поле `code` — символьный код статьи. Если он не задан, в `code` возвращается числовой идентификатор.

Если рубрика не найдена:

```json
{
  "success": false,
  "message": "Рубрика не найдена."
}
```

HTTP-код: `404`.

## Статья

Возвращает одну статью по рубрике и статье. Оба сегмента — `id` или символьный код (если он задан).

```http
GET /api/blog/{rubric}/{article}/
```

### Вызов

```bash
curl -X GET "https://lancy.dev.ru/api/blog/3/12/"
```

```bash
curl -X GET "https://lancy.dev.ru/api/blog/programming/12/" \
  -H "Authorization: Bearer <token>"
```

### Ответ

```json
{
  "success": true,
  "item": {
    "id": 12,
    "title": "Как устроен этот сайт",
    "preview_text": "Кратко о стеке и структуре проекта.",
    "code": "12",
    "published_at": "2026-08-10 14:20:00",
    "status": "published",
    "detail_text": "<p>Полный текст статьи.</p>",
    "seo": {
      "title": "Как устроен этот сайт",
      "description": "Стек и структура личного сайта.",
      "keywords": "php, сайт",
      "robots_index": true,
      "robots_follow": true
    }
  }
}
```

Если статья или рубрика не найдены:

```json
{
  "success": false,
  "message": "Статья не найдена."
}
```

HTTP-код: `404`.

## Создание статьи

Создаёт черновик в указанной рубрике. Статья сразу неактивна (`status: draft`) и появляется в списке этой рубрики. Доступно ролям `ai-agent` и `admin`.

```http
POST /api/blog/rubrics/{id или код рубрики}/create/
Authorization: Bearer <token>
Content-Type: application/json
```

| Поле           | Обязательное | Описание                                                                 |
|----------------|--------------|--------------------------------------------------------------------------|
| `title`        | да           | заголовок, до 255 символов                                               |
| `code`         | нет          | символьный код: латиница, цифры, `-` и `_`. Если пустой — собирается из заголовка |
| `preview_text` | нет          | анонс, до 500 символов                                                   |

### Вызов

```bash
curl -X POST "https://lancy.dev.ru/api/blog/rubrics/programming/create/" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"Как устроен этот сайт\",\"code\":\"how-this-site-works\",\"preview_text\":\"Кратко о стеке и структуре проекта.\"}"
```

По id рубрики:

```bash
curl -X POST "https://lancy.dev.ru/api/blog/rubrics/3/create/" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"Как устроен этот сайт\",\"preview_text\":\"Кратко о стеке и структуре проекта.\"}"
```

### Ответ

HTTP-код: `201`.

```json
{
  "success": true,
  "item": {
    "id": 12,
    "title": "Как устроен этот сайт",
    "preview_text": "Кратко о стеке и структуре проекта.",
    "code": "how-this-site-works",
    "published_at": null,
    "status": "draft"
  }
}
```

## Редактирование SEO

Обновляет SEO-настройки статьи. Непереданные поля не меняются. Доступно ролям `ai-agent` и `admin`.

```http
POST /api/blog/articles/{id или символьный код}/edit/seo/
Authorization: Bearer <token>
Content-Type: application/json
```

| Поле            | Обязательное | Описание                                      |
|-----------------|--------------|-----------------------------------------------|
| `title`         | нет          | title в браузере, до 255 символов             |
| `description`   | нет          | meta description, до 320 символов             |
| `keywords`      | нет          | ключевые слова, до 500 символов               |
| `robots_index`  | нет          | индексация: `true` / `false`                  |
| `robots_follow` | нет          | следование по ссылкам: `true` / `false`       |

### Вызов

```bash
curl -X POST "https://lancy.dev.ru/api/blog/articles/12/edit/seo/" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d "{\"title\":\"Как устроен этот сайт\",\"description\":\"Стек и структура личного сайта.\",\"keywords\":\"php, сайт\",\"robots_index\":true,\"robots_follow\":true}"
```

### Ответ

```json
{
  "success": true,
  "seo": {
    "title": "Как устроен этот сайт",
    "description": "Стек и структура личного сайта.",
    "keywords": "php, сайт",
    "robots_index": true,
    "robots_follow": true
  }
}
```

## Редактирование preview-текста

Обновляет анонс статьи. Доступно ролям `ai-agent` и `admin`.

```http
POST /api/blog/articles/{id или символьный код}/edit/preview-text/
Authorization: Bearer <token>
Content-Type: application/json
```

| Поле           | Обязательное | Описание                    |
|----------------|--------------|-----------------------------|
| `preview_text` | нет          | анонс, до 500 символов      |

### Вызов

```bash
curl -X POST "https://lancy.dev.ru/api/blog/articles/12/edit/preview-text/" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d "{\"preview_text\":\"Кратко о стеке и структуре проекта.\"}"
```

### Ответ

```json
{
  "success": true,
  "item": {
    "id": 12,
    "title": "Как устроен этот сайт",
    "preview_text": "Кратко о стеке и структуре проекта.",
    "code": "how-this-site-works",
    "published_at": null,
    "status": "draft"
  }
}
```

## Редактирование preview-изображения

Загружает и сохраняет картинку анонса статьи. Доступно ролям `ai-agent` и `admin`.

```http
POST /api/blog/articles/{id или символьный код}/edit/preview-image/
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

| Поле   | Тип  | Описание                               |
|--------|------|----------------------------------------|
| `file` | файл | изображение JPG, PNG, GIF или WEBP     |

### Вызов

```bash
curl -X POST "https://lancy.dev.ru/api/blog/articles/12/edit/preview-image/" \
  -H "Authorization: Bearer <token>" \
  -F "file=@preview.png"
```

### Ответ

```json
{
  "success": true,
  "url": "https://lancy.dev.ru/upload/images/blog/articles/blog_article_preview_12_20260820_101500_a1b2c3d4.png"
}
```

## Редактирование detail-изображения

Загружает и сохраняет детальную картинку статьи. Доступно ролям `ai-agent` и `admin`.

```http
POST /api/blog/articles/{id или символьный код}/edit/detail-image/
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

| Поле   | Тип  | Описание                               |
|--------|------|----------------------------------------|
| `file` | файл | изображение JPG, PNG, GIF или WEBP     |

### Вызов

```bash
curl -X POST "https://lancy.dev.ru/api/blog/articles/12/edit/detail-image/" \
  -H "Authorization: Bearer <token>" \
  -F "file=@detail.png"
```

### Ответ

```json
{
  "success": true,
  "url": "https://lancy.dev.ru/upload/images/blog/articles/blog_article_detail_12_20260820_101530_b2c3d4e5.png"
}
```

## Загрузка изображения

Альтернативный способ загрузить preview- или detail-картинку, если статья задаётся полем, а не путём. Доступно ролям `ai-agent` и `admin`.

```http
POST /api/blog/media/
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

| Поле           | Тип     | Описание                                                                 |
|----------------|---------|--------------------------------------------------------------------------|
| `file`         | файл    | изображение JPG, PNG, GIF или WEBP                                       |
| `article_code` | строка  | символьный код статьи или её числовой `id`                               |
| `type`         | строка  | `preview` — анонс, `detail` — детальная страница                         |

Файл сохраняется в `/upload/images/blog/articles/` и записывается в статью.

### Вызов

```bash
curl -X POST "https://lancy.dev.ru/api/blog/media/" \
  -H "Authorization: Bearer <token>" \
  -F "file=@preview.png" \
  -F "article_code=12" \
  -F "type=preview"
```

### Ответ

```json
{
  "success": true,
  "url": "https://lancy.dev.ru/upload/images/blog/articles/blog_article_preview_12_20260820_101500_a1b2c3d4.png"
}
```

Расширение в `url` соответствует типу загруженного файла.

## Коды ответов

| Код | Когда                                      |
|-----|--------------------------------------------|
| 200 | запрос выполнен                            |
| 201 | статья создана                             |
| 204 | ответ на CORS `OPTIONS`                    |
| 400 | некорректные поля запроса или файл         |
| 401 | нет токена или токен недействителен        |
| 403 | роль ниже `ai-agent`                       |
| 404 | рубрика или статья не найдены              |
| 500 | внутренняя ошибка                          |

Ошибки возвращаются в виде:

```json
{
  "success": false,
  "message": "Текст ошибки."
}
```
