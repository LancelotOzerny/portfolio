# API блога

JSON API сайта [lancy.dev](https://lancy.dev) для рубрик, статей и загрузки изображений.

Базовый адрес: `https://lancy.dev`  
Формат ответа: `application/json; charset=utf-8`  
Все пути заканчиваются `/`.

## Содержание

1. [Авторизация](#авторизация)
2. [Статусы материалов](#статусы-материалов)
3. [Рубрики](#рубрики)
4. [Список статей](#список-статей)
5. [Статья](#статья)
6. [Загрузка изображения](#загрузка-изображения)
7. [Коды ответов](#коды-ответов)

## Авторизация

Чтение опубликованных рубрик и статей доступно без токена.

Черновики и загрузка изображений доступны ролям `ai-agent` и выше. Токен передаётся в заголовке:

```http
Authorization: Bearer <token>
```

Токен выпускается в админке: **Пользователи → карточка пользователя**.

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
curl -X GET "https://lancy.dev/api/blog/rubrics/"
```

С черновиками:

```bash
curl -X GET "https://lancy.dev/api/blog/rubrics/" \
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
curl -X GET "https://lancy.dev/api/blog/articles/"
```

Статьи рубрики по коду:

```bash
curl -X GET "https://lancy.dev/api/blog/articles/?rubric=programming"
```

Статьи рубрики по id, включая черновики:

```bash
curl -X GET "https://lancy.dev/api/blog/articles/?rubric=3" \
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
curl -X GET "https://lancy.dev/api/blog/3/12/"
```

```bash
curl -X GET "https://lancy.dev/api/blog/programming/12/" \
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

## Загрузка изображения

Загружает обложку или детальную картинку статьи. Доступно ролям `ai-agent` и выше.

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
curl -X POST "https://lancy.dev/api/blog/media/" \
  -H "Authorization: Bearer <token>" \
  -F "file=@preview.png" \
  -F "article_code=12" \
  -F "type=preview"
```

### Ответ

```json
{
  "success": true,
  "url": "https://lancy.dev/upload/images/blog/articles/blog_article_preview_12_20260820_101500_a1b2c3d4.png"
}
```

Расширение в `url` соответствует типу загруженного файла.

## Коды ответов

| Код | Когда                                      |
|-----|--------------------------------------------|
| 200 | запрос выполнен                            |
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
