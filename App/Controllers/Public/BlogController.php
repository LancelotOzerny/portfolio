<?php
namespace Controllers\Public;

use App\Services\Blog\ArticleContentSanitizer;
use App\Services\Blog\BlogDateFormatter;
use App\Services\Site\EditModeService;
use App\Services\Seo\SeoContext;
use App\Services\Security\CsrfService;
use Models\BlogArticlesModel;
use Models\BlogTopicsModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class BlogController extends BaseController
{
	public function index(): void
	{
		$this->setSeo(SeoContext::page('blog'));
		Template::getInstance()->setParam('title', 'Блог');
		Template::getInstance()->setParam('subtitle', 'Темы статей и заметок');
		Template::getInstance()->setParam('show_contact_cta', false);

		Template::getInstance()->showHeader();
		$this->render('index', [
			'is_admin' => Auth::getInstance()->isAdmin(),
			'edit_mode' => $this->isEditMode(),
		]);
		Template::getInstance()->showFooter();
	}

	public function topic(string $topic): void
	{
		$topicData = $this->findTopic($topic);

		$this->setSeo(SeoContext::custom('/blog/' . $topic . '/', [
			'title' => $topicData['name'] ?? 'Тема не найдена',
			'description' => $topicData['description'] ?? 'Тестовая тема блога.',
			'robots_index' => $topicData !== null,
		]));

		Template::getInstance()->setParam('title', $topicData['name'] ?? 'Тема не найдена');
		Template::getInstance()->setParam('subtitle', 'Статьи выбранной темы');
		Template::getInstance()->setParam('show_contact_cta', false);

		Template::getInstance()->showHeader();
		$this->render('topic', [
			'topic' => $topicData,
			'is_admin' => Auth::getInstance()->isAdmin(),
			'edit_mode' => $this->isEditMode(),
		]);
		Template::getInstance()->showFooter();
	}

	public function detail(string $topic, string $article): void
	{
		$topicData = $this->findTopic($topic);
		$articleData = $topicData !== null ? $this->findArticle($topicData, $article) : null;

		$this->setSeo(SeoContext::custom('/blog/' . $topic . '/' . $article . '/', [
			'title' => $articleData['title'] ?? 'Статья не найдена',
			'description' => $articleData['preview'] ?? 'Тестовая статья блога.',
			'robots_index' => $articleData !== null,
		]));

		Template::getInstance()->setParam('title', $articleData['title'] ?? 'Статья не найдена');
		Template::getInstance()->setParam('subtitle', 'Детальная страница статьи');
		Template::getInstance()->setParam('show_contact_cta', false);

		Template::getInstance()->showHeader();
		$this->render('detail', [
			'topic' => $topicData,
			'article' => $articleData,
			'is_admin' => Auth::getInstance()->isAdmin(),
			'edit_mode' => $this->isEditMode(),
			'csrf_token' => (new CsrfService())->getToken(),
			'save_success' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'save_error' => isset($_GET['error']) ? (string) $_GET['error'] : '',
		]);
		Template::getInstance()->showFooter();
	}

	public function updateDetail(string $topic, string $article): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			$this->redirectToArticle($topic, $article, 'Invalid CSRF token.');
			return;
		}

		if (!ctype_digit($article)) {
			$this->redirectToArticle($topic, $article, 'Only database articles can be edited here.');
			return;
		}

		$model = new BlogArticlesModel();
		$articleId = (int) $article;

		try {
			$articleData = $model->findById($articleId);
		} catch (Throwable) {
			$articleData = null;
		}

		if ($articleData === null) {
			$this->redirectToArticle($topic, $article, 'Article not found.');
			return;
		}

		$detailText = (new ArticleContentSanitizer())->sanitize((string) ($_POST['detail_text'] ?? ''));

		try {
			$topicIds = $model->findTopicIdsByArticleId($articleId);
		} catch (Throwable) {
			$topicIds = [];
		}

		$topicId = (int) ($topicIds[0] ?? (int) ($articleData->topic_id ?? 0));
		if ($topicId <= 0) {
			$this->redirectToArticle($topic, $article, 'Article topic was not found.');
			return;
		}

		try {
			if (!$model->updateEditorData(
				$articleId,
				$topicId,
				(string) ($articleData->title ?? ''),
				(int) ($articleData->enabled ?? 0),
				(string) ($articleData->preview_text ?? ''),
				(string) ($articleData->preview_image_path ?? ''),
				$detailText,
				(string) ($articleData->detail_image_path ?? ''),
				(string) ($articleData->author ?? '')
			)) {
				throw new \RuntimeException('Unable to save article.');
			}
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->redirectToArticle($topic, $article, $message !== '' ? $message : 'Unable to save article.');
			return;
		}

		header('Location: /blog/' . rawurlencode($topic) . '/' . rawurlencode($article) . '/?edit=true&saved=1');
	}

	public function uploadDetailImage(string $topic, string $article): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!$this->ensureUploadRequestIsAllowed($article)) {
			return;
		}

		try {
			$url = $this->saveInlineArticleImage((int) $article, 'image');
			echo json_encode([
				'success' => 1,
				'file' => [
					'url' => $url,
				],
			]);
		} catch (Throwable $e) {
			http_response_code(400);
			$message = trim($e->getMessage());
			echo json_encode([
				'success' => 0,
				'error' => $message !== '' ? $message : 'Unable to upload image.',
			]);
		}
	}

	public function uploadDetailFile(string $topic, string $article): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!$this->ensureUploadRequestIsAllowed($article)) {
			return;
		}

		try {
			$file = $this->saveInlineArticleFile((int) $article, 'file');
			echo json_encode([
				'success' => 1,
				'file' => $file,
			]);
		} catch (Throwable $e) {
			http_response_code(400);
			$message = trim($e->getMessage());
			echo json_encode([
				'success' => 0,
				'error' => $message !== '' ? $message : 'Unable to upload file.',
			]);
		}
	}

	private function findTopic(string $slug): ?array
	{
		if (ctype_digit($slug)) {
			try {
				$topic = (new BlogTopicsModel())->findById((int) $slug);
			} catch (Throwable) {
				$topic = null;
			}

			if ($topic !== null) {
				try {
					$articles = (new BlogArticlesModel())->findByTopicId((int) $slug, !$this->isEditMode());
				} catch (Throwable) {
					$articles = [];
				}

				return [
					'name' => (string) ($topic->title ?? 'Без названия'),
					'slug' => (string) ($topic->id ?? $slug),
					'description' => trim((string) ($topic->detail_text ?? '')) !== ''
						? (string) ($topic->detail_text ?? '')
						: (string) ($topic->preview_text ?? ''),
					'detail_image_path' => (string) ($topic->detail_image_path ?? ''),
					'articles' => $this->mapDbArticles($articles),
				];
			}
		}

		foreach ($this->getTopics() as $topic) {
			if ($topic['slug'] === $slug) {
				return $topic;
			}
		}

		return null;
	}

	private function isEditMode(): bool
	{
		return (new EditModeService())->isActive();
	}

	private function mapDbArticles(array $articles): array
	{
		$result = [];
		$dateFormatter = new BlogDateFormatter();

		foreach ($articles as $article) {
			$articleId = (int) ($article->id ?? 0);
			if ($articleId <= 0) {
				continue;
			}

			$result[] = [
				'id' => $articleId,
				'topic_id' => (int) ($article->topic_id ?? 0),
				'title' => (string) ($article->title ?? 'Без названия'),
				'slug' => (string) $articleId,
				'enabled' => (int) ($article->enabled ?? 0) === 1,
				'detail_image' => trim((string) ($article->detail_image_path ?? '')) !== ''
					? (string) ($article->detail_image_path ?? '')
					: '/Templates/Inner/img/no-image.webp',
				'image' => trim((string) ($article->preview_image_path ?? '')) !== ''
					? (string) ($article->preview_image_path ?? '')
					: '/Templates/Inner/img/no-image.webp',
				'date' => $dateFormatter->format((string) ($article->created_at ?? '')),
				'rating' => 0,
				'preview' => (string) ($article->preview_text ?? ''),
				'content' => [(string) ($article->detail_text ?? '')],
				'detail_text' => (string) ($article->detail_text ?? ''),
				'comments' => [],
			];
		}

		return $result;
	}

	private function findArticle(array $topic, string $slug): ?array
	{
		foreach ($topic['articles'] as $article) {
			if ($article['slug'] === $slug) {
				return $article;
			}
		}

		return null;
	}

	private function redirectToArticle(string $topic, string $article, string $error): void
	{
		header('Location: /blog/' . rawurlencode($topic) . '/' . rawurlencode($article) . '/?edit=true&error=' . rawurlencode($error));
	}

	private function ensureUploadRequestIsAllowed(string $article): bool
	{
		if (!$this->isAdmin()) {
			http_response_code(403);
			echo json_encode(['success' => 0, 'error' => 'Access denied.']);
			return false;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			http_response_code(400);
			echo json_encode(['success' => 0, 'error' => 'Invalid CSRF token.']);
			return false;
		}

		if (!ctype_digit($article)) {
			http_response_code(400);
			echo json_encode(['success' => 0, 'error' => 'Invalid article id.']);
			return false;
		}

		return true;
	}

	private function saveInlineArticleImage(int $articleId, string $fileKey): string
	{
		$file = $_FILES[$fileKey] ?? null;
		if (!is_array($file)) {
			throw new \RuntimeException('Image file was not sent.');
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new \RuntimeException('Image upload error.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new \RuntimeException('Invalid uploaded image.');
		}

		$mime = $this->detectImageMimeType($tmpPath);
		$allowedMimeToExt = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/webp' => 'webp',
		];

		if (!isset($allowedMimeToExt[$mime])) {
			throw new \RuntimeException('Only JPG/PNG/GIF/WEBP images are allowed.');
		}

		$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
		if ($documentRoot === '') {
			throw new \RuntimeException('Document root is not configured.');
		}

		$uploadDir = $documentRoot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'articles';
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
			throw new \RuntimeException('Unable to create upload directory.');
		}

		$fileName = sprintf(
			'article_%d_%s_%s.%s',
			$articleId,
			date('Ymd_His'),
			bin2hex(random_bytes(4)),
			$allowedMimeToExt[$mime]
		);
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new \RuntimeException('Unable to save uploaded image.');
		}

		return '/upload/articles/' . $fileName;
	}

	private function saveInlineArticleFile(int $articleId, string $fileKey): array
	{
		$file = $_FILES[$fileKey] ?? null;
		if (!is_array($file)) {
			throw new \RuntimeException('File was not sent.');
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new \RuntimeException('File upload error.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new \RuntimeException('Invalid uploaded file.');
		}

		$originalName = trim((string) ($file['name'] ?? ''));
		$extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
		$allowedExtensions = ['docx', 'pdf', 'txt'];
		if (!in_array($extension, $allowedExtensions, true)) {
			throw new \RuntimeException('Only DOCX/TXT/PDF files are allowed.');
		}

		$mime = $this->detectImageMimeType($tmpPath);
		$allowedMimeByExtension = [
			'docx' => [
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'application/zip',
				'application/octet-stream',
			],
			'pdf' => [
				'application/pdf',
				'application/x-pdf',
				'application/octet-stream',
			],
			'txt' => [
				'text/plain',
				'text/x-plain',
				'application/octet-stream',
			],
		];

		if ($mime !== '' && !in_array($mime, $allowedMimeByExtension[$extension], true)) {
			throw new \RuntimeException('Uploaded file type does not match its extension.');
		}

		$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
		if ($documentRoot === '') {
			throw new \RuntimeException('Document root is not configured.');
		}

		$uploadDir = $documentRoot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'articles';
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
			throw new \RuntimeException('Unable to create upload directory.');
		}

		$fileName = sprintf(
			'article_%d_file_%s_%s.%s',
			$articleId,
			date('Ymd_His'),
			bin2hex(random_bytes(4)),
			$extension
		);
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new \RuntimeException('Unable to save uploaded file.');
		}

		return [
			'url' => '/upload/articles/' . $fileName,
			'name' => $originalName !== '' ? $originalName : $fileName,
			'extension' => $extension,
		];
	}

	private function detectImageMimeType(string $filePath): string
	{
		$mime = '';

		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			if ($finfo !== false) {
				$detected = finfo_file($finfo, $filePath);
				finfo_close($finfo);

				if (is_string($detected)) {
					$mime = $detected;
				}
			}
		}

		if ($mime === '' && function_exists('mime_content_type')) {
			$detected = mime_content_type($filePath);
			if (is_string($detected)) {
				$mime = $detected;
			}
		}

		if ($mime === '' && function_exists('getimagesize')) {
			$imageInfo = @getimagesize($filePath);
			if (is_array($imageInfo) && isset($imageInfo['mime']) && is_string($imageInfo['mime'])) {
				$mime = $imageInfo['mime'];
			}
		}

		return strtolower(trim($mime));
	}

	private function ensureAdmin(): bool
	{
		if ($this->isAdmin()) {
			return true;
		}

		header('Location: /admin/login/');
		return false;
	}

	private function isAdmin(): bool
	{
		$auth = Auth::getInstance();
		return $auth->getCurrentUser() !== null && $auth->isAdmin();
	}

	private function getTopics(): array
	{
		return [
			[
				'name' => 'Видеоигры',
				'slug' => 'videogames',
				'image' => '/upload/images/blog/topic-videogames.svg',
				'description' => 'Заметки о сюжетах, механиках, жанрах и личных впечатлениях от игр.',
				'articles' => [
					[
						'title' => 'Почему короткие игры снова цепляют',
						'slug' => 'short-games',
						'image' => '/upload/images/blog/article-short-games.svg',
						'date' => '18 июля 2026',
						'rating' => 8,
						'preview' => 'Небольшая заметка о том, почему компактные игровые истории часто запоминаются сильнее огромных миров.',
						'content' => [
							'Короткие игры выигрывают за счет темпа. Они быстрее показывают идею, не перегружают игрока лишними системами и оставляют после себя цельное впечатление.',
							'В таких проектах особенно заметны работа с атмосферой, визуальным ритмом и точностью игровых ситуаций.',
						],
						'comments' => [
							['author' => 'Алексей', 'text' => 'Люблю такие проекты: прошел за вечер, но думаю о них неделю.'],
							['author' => 'Мария', 'text' => 'Согласна, иногда меньше контента означает больше эмоций.'],
						],
					],
					[
						'title' => 'Что делает игровую механику честной',
						'slug' => 'fair-game-mechanics',
						'image' => '/upload/images/blog/article-game-mechanics.svg',
						'date' => '25 июля 2026',
						'rating' => 9,
						'preview' => 'Разбираю, почему хорошие правила должны быть понятными, последовательными и уважать время игрока.',
						'content' => [
							'Честная механика не обязана быть легкой. Важно, чтобы игрок понимал причину ошибки и видел путь к улучшению.',
							'Когда игра стабильно соблюдает собственные правила, сложность воспринимается как вызов, а не как случайность.',
						],
						'comments' => [
							['author' => 'Игорь', 'text' => 'Хорошее наблюдение про понятную причину поражения.'],
							['author' => 'Светлана', 'text' => 'Это особенно заметно в платформерах и тактических играх.'],
						],
					],
				],
			],
			[
				'name' => 'IT и программирование',
				'slug' => 'it-programming',
				'image' => '/upload/images/blog/topic-programming.svg',
				'description' => 'Практические мысли о разработке, архитектуре, PHP и рабочих процессах.',
				'articles' => [
					[
						'title' => 'Минимальные правки как навык разработчика',
						'slug' => 'minimal-changes',
						'image' => '/upload/images/blog/article-minimal-changes.svg',
						'date' => '12 июля 2026',
						'rating' => 10,
						'preview' => 'О том, почему точечные изменения часто надежнее больших рефакторингов без необходимости.',
						'content' => [
							'Минимальная правка хороша не потому, что она маленькая, а потому что она уважает уже работающую систему.',
							'Сначала стоит понять текущие границы проекта, а уже потом выбирать место для изменения.',
						],
						'comments' => [
							['author' => 'Дмитрий', 'text' => 'Очень близко к реальной командной разработке.'],
							['author' => 'Ольга', 'text' => 'Да, особенно когда проект уже в продакшене.'],
						],
					],
					[
						'title' => 'Зачем PHP-проекту простая структура',
						'slug' => 'simple-php-structure',
						'image' => '/upload/images/blog/article-php-structure.svg',
						'date' => '29 июля 2026',
						'rating' => 8,
						'preview' => 'Коротко о том, как понятные контроллеры, модели и шаблоны помогают быстрее развивать сайт.',
						'content' => [
							'Простая структура снижает стоимость каждого следующего изменения. Разработчик быстрее находит нужный участок и меньше рискует задеть лишнее.',
							'Даже без тяжелого фреймворка проект может оставаться аккуратным, если в нем есть понятные соглашения.',
						],
						'comments' => [
							['author' => 'Никита', 'text' => 'Хороший аргумент в пользу умеренности в архитектуре.'],
							['author' => 'Елена', 'text' => 'Главное, чтобы соглашения действительно соблюдались.'],
						],
					],
				],
			],
		];
	}
}
