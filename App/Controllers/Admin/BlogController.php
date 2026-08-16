<?php

namespace Controllers\Admin;

use App\Services\Blog\ArticleTransferService;
use App\Services\Blog\BlogArticlePublicationService;
use App\Services\Blog\BlogSeoService;
use App\Services\Blog\SymbolicCodeService;
use Models\BlogArticleCommentsModel;
use Models\BlogArticlesModel;
use Models\BlogTopicsModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class BlogController extends BaseController
{
	private const FLASH_KEY = 'admin_blog_flash';

	public function index(): void
	{
		$this->rubrics();
	}

	public function rubrics(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$flash = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		Template::getInstance()->setParam('title', 'Рубрики блога');
		Template::getInstance()->showHeader();
		$this->render('rubrics', [
			'flash' => is_array($flash) ? $flash : null,
		]);
		Template::getInstance()->showFooter();
	}

	public function articles(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$flash = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		$articles = [];
		$error = '';

		try {
			$articles = (new BlogArticlesModel())->findAllWithTopic();
		} catch (Throwable $exception) {
			$error = $exception->getMessage();
		}

		Template::getInstance()->setParam('title', 'Статьи блога');
		Template::getInstance()->showHeader();
		$this->render('articles', [
			'articles' => $articles,
			'error' => $error,
			'flash' => is_array($flash) ? $flash : null,
		]);
		Template::getInstance()->showFooter();
	}

	public function comments(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$comments = [];
		$error = '';
		$codeService = new SymbolicCodeService();

		try {
			$rows = (new BlogArticleCommentsModel())->findAllForAdmin();
			foreach ($rows as $row) {
				$commentId = (int) ($row->id ?? 0);
				$articleId = (int) ($row->article_id ?? 0);
				$topicId = (int) ($row->topic_id ?? 0);
				$author = trim((string) ($row->updated_by_name ?? ''));
				$articleUrl = '';

				if ($articleId > 0 && $topicId > 0) {
					$topicSegment = $codeService->resolvePublicSegment(
						(string) ($row->topic_code ?? ''),
						$topicId
					);
					$articleSegment = $codeService->resolvePublicSegment(
						(string) ($row->article_code ?? ''),
						$articleId
					);
					$articleUrl = '/blog/' . $topicSegment . '/' . $articleSegment . '/';
				}

				$comments[] = [
					'id' => $commentId,
					'author' => $author !== '' ? $author : 'Аноним',
					'text' => (string) ($row->comment_text ?? ''),
					'article_id' => $articleId,
					'article_url' => $articleUrl,
				];
			}
		} catch (Throwable $exception) {
			$error = $exception->getMessage();
		}

		Template::getInstance()->setParam('title', 'Комментарии блога');
		Template::getInstance()->showHeader();
		$this->render('comments', [
			'comments' => $comments,
			'error' => $error,
		]);
		Template::getInstance()->showFooter();
	}

	public function articleCreate(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$topics = $this->loadTopics();

		Template::getInstance()->setParam('title', 'Создание статьи блога');
		Template::getInstance()->showHeader();
		$this->render('article-edit', [
			'article' => null,
			'topics' => $topics,
			'selectedTopicIds' => $this->resolvePreselectedTopicIds($topics),
			'seoForm' => $this->emptySeoForm(),
			'saveSuccess' => false,
			'saveError' => isset($_GET['error']) ? (string) $_GET['error'] : '',
		]);
		Template::getInstance()->showFooter();
	}

	public function articleStore(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$normalized = $this->normalizeArticleInput();
		if ($normalized['error'] !== '') {
			header('Location: /admin/content/blog/articles/create/?error=' . rawurlencode($normalized['error']));
			return;
		}

		$model = new BlogArticlesModel();
		$articleId = 0;

		try {
			$articleId = $model->createForAdmin($normalized['topic_id'], $normalized['title'], $normalized['code']);
		} catch (Throwable) {
		}

		if ($articleId <= 0) {
			header('Location: /admin/content/blog/articles/create/?error=' . rawurlencode('Не удалось создать статью.'));
			return;
		}

		try {
			$previewImagePath = $this->saveArticleImageUpload($articleId, 'preview_image_file', '', 'preview');
			$detailImagePath = $this->saveArticleImageUpload($articleId, 'detail_image_file', '', 'detail');

			$model->updateEditorData(
				$articleId,
				$normalized['topic_id'],
				$normalized['title'],
				$normalized['code'],
				$normalized['enabled'],
				$normalized['preview_text'],
				$previewImagePath,
				$normalized['detail_text'],
				$detailImagePath,
				$normalized['author']
			);

			if (!$model->replaceTopicIds($articleId, $normalized['topic_ids'])) {
				throw new \RuntimeException('Не удалось сохранить рубрики статьи.');
			}

			$this->saveBlogSeo(BlogSeoService::TYPE_ARTICLE, (string) $articleId);
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			if ($message === '') {
				$message = 'Статья создана, но часть данных не удалось сохранить.';
			}

			header('Location: /admin/content/blog/articles/' . $articleId . '/?error=' . rawurlencode($message));
			return;
		}

		header('Location: /admin/content/blog/articles/' . $articleId . '/');
	}

	public function articleEdit(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$article = (new BlogArticlesModel())->findById($id);
		} catch (Throwable) {
			$article = null;
		}

		if ($article === null) {
			header('Location: /admin/content/blog/articles/');
			return;
		}

		$flash = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		Template::getInstance()->setParam('title', 'Редактирование статьи блога ' . $id);
		try {
			$selectedTopicIds = (new BlogArticlesModel())->findTopicIdsByArticleId($id);
		} catch (Throwable) {
			$selectedTopicIds = [];
		}

		$publicationService = new BlogArticlePublicationService();

		Template::getInstance()->showHeader();
		$this->render('article-edit', [
			'article' => $article,
			'topics' => $this->loadTopics(),
			'selectedTopicIds' => $selectedTopicIds,
			'seoForm' => (new BlogSeoService())->getFormData(BlogSeoService::TYPE_ARTICLE, (string) $id),
			'saveSuccess' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'saveError' => isset($_GET['error']) ? (string) $_GET['error'] : '',
			'flash' => is_array($flash) ? $flash : null,
			'publicationDatetime' => $publicationService->getPublicationDatetime($article),
			'scheduledDatetime' => $publicationService->getScheduledDatetime($article),
			'commentsCount' => (new BlogArticleCommentsModel())->countByArticleId($id),
		]);
		Template::getInstance()->showFooter();
	}

	public function articleUpdate(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$model = new BlogArticlesModel();

		try {
			$article = $model->findById($id);
		} catch (Throwable) {
			$article = null;
		}

		if ($article === null) {
			header('Location: /admin/content/blog/articles/');
			return;
		}

		$normalized = $this->normalizeArticleInput($id);
		if ($normalized['error'] !== '') {
			header('Location: /admin/content/blog/articles/' . $id . '/?error=' . rawurlencode($normalized['error']));
			return;
		}

		$previewImagePath = trim((string) ($_POST['preview_image_path_existing'] ?? (string) ($article->preview_image_path ?? '')));
		$detailImagePath = trim((string) ($_POST['detail_image_path_existing'] ?? (string) ($article->detail_image_path ?? '')));

		try {
			$previewImagePath = $this->saveArticleImageUpload($id, 'preview_image_file', $previewImagePath, 'preview');
			$detailImagePath = $this->saveArticleImageUpload($id, 'detail_image_file', $detailImagePath, 'detail');

			if (!$model->updateEditorData(
				$id,
				$normalized['topic_id'],
				$normalized['title'],
				$normalized['code'],
				$normalized['enabled'],
				$normalized['preview_text'],
				$previewImagePath,
				$normalized['detail_text'],
				$detailImagePath,
				$normalized['author']
			)) {
				throw new \RuntimeException('Не удалось сохранить изменения.');
			}

			if (!$model->replaceTopicIds($id, $normalized['topic_ids'])) {
				throw new \RuntimeException('Не удалось сохранить рубрики статьи.');
			}

			$this->saveBlogSeo(BlogSeoService::TYPE_ARTICLE, (string) $id);

			header('Location: /admin/content/blog/articles/' . $id . '/?saved=1');
			return;
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			if ($message === '') {
				$message = 'Не удалось сохранить изменения.';
			}

			header('Location: /admin/content/blog/articles/' . $id . '/?error=' . rawurlencode($message));
			return;
		}
	}

	public function articleDelete(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			if (!(new BlogArticlesModel())->deleteById($id)) {
				throw new \RuntimeException('Не удалось удалить статью.');
			}

			$this->setFlash(true, 'Статья удалена.');
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->setFlash(false, $message !== '' ? $message : 'Не удалось удалить статью.');
		}

		header('Location: /admin/content/blog/articles/');
	}

	public function articleExport(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$article = (new BlogArticlesModel())->findById($id);
		} catch (Throwable) {
			$article = null;
		}

		if ($article === null) {
			$this->setFlash(false, 'Статья не найдена.');
			header('Location: /admin/content/blog/articles/');
			return;
		}

		try {
			$transfer = new ArticleTransferService();
			$payload = $transfer->exportJson($article);
			$fileName = $transfer->exportFileName($article);
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->setFlash(false, $message !== '' ? $message : 'Не удалось экспортировать статью.');
			header('Location: /admin/content/blog/articles/');
			return;
		}

		header('Content-Type: application/json; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header('Cache-Control: no-store');
		echo $payload;
		exit;
	}

	public function articleImport(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$articleId = (new ArticleTransferService())->importFromUpload($_FILES['article_file'] ?? []);
			$this->setFlash(true, 'Статья импортирована как скрытая и неопубликованная.');
			header('Location: /admin/content/blog/articles/' . $articleId . '/');
			return;
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->setFlash(false, $message !== '' ? $message : 'Не удалось импортировать статью.');
			header('Location: /admin/content/blog/articles/');
			return;
		}
	}

	public function articlePublish(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$redirect = $this->resolvePublicationRedirect($id);

		try {
			$article = (new BlogArticlesModel())->findById($id);
		} catch (Throwable) {
			$article = null;
		}

		if ($article === null) {
			$this->setFlash(false, 'Статья не найдена.');
			header('Location: ' . $redirect);
			return;
		}

		if ((new BlogArticlePublicationService())->publishNow($id)) {
			$this->setFlash(true, 'Статья опубликована.');
		} else {
			$this->setFlash(false, 'Не удалось опубликовать статью.');
		}

		header('Location: ' . $redirect);
	}

	public function articleSchedule(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$redirect = $this->resolvePublicationRedirect($id);
		$publishedAt = trim((string) ($_POST['published_at'] ?? ''));

		if ($publishedAt === '') {
			$this->setFlash(false, 'Укажите время публикации.');
			header('Location: ' . $redirect);
			return;
		}

		try {
			$article = (new BlogArticlesModel())->findById($id);
		} catch (Throwable) {
			$article = null;
		}

		if ($article === null) {
			$this->setFlash(false, 'Статья не найдена.');
			header('Location: ' . $redirect);
			return;
		}

		$service = new BlogArticlePublicationService();
		if ($service->schedule($id, $publishedAt)) {
			$this->setFlash(true, 'Время публикации сохранено.');
		} else {
			$this->setFlash(false, 'Не удалось сохранить время публикации.');
		}

		header('Location: ' . $redirect);
	}

	public function create(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Создание рубрики блога');
		Template::getInstance()->showHeader();
		$this->render('edit', [
			'topic' => null,
			'seoForm' => $this->emptySeoForm(),
			'saveSuccess' => false,
			'saveError' => isset($_GET['error']) ? (string) $_GET['error'] : '',
		]);
		Template::getInstance()->showFooter();
	}

	public function store(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$title = trim((string) ($_POST['title'] ?? ''));
		$codeService = new SymbolicCodeService();
		$code = $codeService->normalize((string) ($_POST['code'] ?? ''));
		if ($code === '') {
			$code = $codeService->fromTitle($title);
		}
		$description = trim((string) ($_POST['description'] ?? ''));
		$detailText = (string) ($_POST['detail_text'] ?? '');
		if ($title === '') {
			header('Location: /admin/content/blog/rubrics/create/?error=' . rawurlencode('Введите название рубрики.'));
			return;
		}

		if (!$codeService->isValid($code)) {
			header('Location: /admin/content/blog/rubrics/create/?error=' . rawurlencode('Символьный код должен содержать только латинские буквы, цифры, "-" и "_".'));
			return;
		}

		if (mb_strlen($description) > 500) {
			header('Location: /admin/content/blog/rubrics/create/?error=' . rawurlencode('Preview текст должен быть не длиннее 500 символов.'));
			return;
		}

		$model = new BlogTopicsModel();

		if ($model->isCodeTaken($code)) {
			header('Location: /admin/content/blog/rubrics/create/?error=' . rawurlencode('Символьный код уже используется.'));
			return;
		}

		$topicId = 0;

		try {
			$topicId = $model->createForAdmin($title, $code);
		} catch (Throwable) {
		}

		if ($topicId <= 0) {
			header('Location: /admin/content/blog/rubrics/create/?error=' . rawurlencode('Не удалось создать рубрику.'));
			return;
		}

		try {
			$imagePath = $this->saveTopicImageUpload($topicId, 'image_file', '');
			$detailImagePath = $this->saveTopicImageUpload($topicId, 'detail_image_file', '', 'detail');
			$model->updateEditorData(
				$topicId,
				$title,
				$code,
				$description,
				$imagePath,
				$detailText,
				$detailImagePath,
				isset($_POST['enabled']) ? 1 : 0
			);

			$this->saveBlogSeo(BlogSeoService::TYPE_TOPIC, (string) $topicId);
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			if ($message === '') {
				$message = 'Рубрика создана, но часть данных не удалось сохранить.';
			}

			header('Location: /admin/content/blog/rubrics/' . $topicId . '/?error=' . rawurlencode($message));
			return;
		}

		header('Location: /admin/content/blog/rubrics/' . $topicId . '/');
	}

	public function edit(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$topic = (new BlogTopicsModel())->findById($id);
		} catch (Throwable) {
			$topic = null;
		}

		if ($topic === null) {
			header('Location: /admin/content/blog/rubrics/');
			return;
		}

		Template::getInstance()->setParam('title', 'Редактирование рубрики блога ' . $id);
		Template::getInstance()->showHeader();
		$this->render('edit', [
			'topic' => $topic,
			'seoForm' => (new BlogSeoService())->getFormData(BlogSeoService::TYPE_TOPIC, (string) $id),
			'saveSuccess' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'saveError' => isset($_GET['error']) ? (string) $_GET['error'] : '',
		]);
		Template::getInstance()->showFooter();
	}

	public function update(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$model = new BlogTopicsModel();

		try {
			$topic = $model->findById($id);
		} catch (Throwable) {
			$topic = null;
		}

		if ($topic === null) {
			header('Location: /admin/content/blog/rubrics/');
			return;
		}

		$title = trim((string) ($_POST['title'] ?? ''));
		$codeService = new SymbolicCodeService();
		$code = $codeService->normalize((string) ($_POST['code'] ?? ''));
		if ($code === '') {
			$code = $codeService->fromTitle($title);
		}
		$description = trim((string) ($_POST['description'] ?? ''));
		$imagePath = trim((string) ($_POST['image_path_existing'] ?? (string) ($topic->image_path ?? '')));
		$detailText = (string) ($_POST['detail_text'] ?? '');
		$detailImagePath = trim((string) ($_POST['detail_image_path_existing'] ?? (string) ($topic->detail_image_path ?? '')));
		$enabled = isset($_POST['enabled']) ? 1 : 0;

		if ($title === '') {
			header('Location: /admin/content/blog/rubrics/' . $id . '/?error=' . rawurlencode('Введите название рубрики.'));
			return;
		}

		if (!$codeService->isValid($code)) {
			header('Location: /admin/content/blog/rubrics/' . $id . '/?error=' . rawurlencode('Символьный код должен содержать только латинские буквы, цифры, "-" и "_".'));
			return;
		}

		if ($model->isCodeTaken($code, $id)) {
			header('Location: /admin/content/blog/rubrics/' . $id . '/?error=' . rawurlencode('Символьный код уже используется.'));
			return;
		}

		if (mb_strlen($description) > 500) {
			header('Location: /admin/content/blog/rubrics/' . $id . '/?error=' . rawurlencode('Preview текст должен быть не длиннее 500 символов.'));
			return;
		}

		try {
			$imagePath = $this->saveTopicImageUpload($id, 'image_file', $imagePath);
			$detailImagePath = $this->saveTopicImageUpload($id, 'detail_image_file', $detailImagePath, 'detail');

			if (!$model->updateEditorData($id, $title, $code, $description, $imagePath, $detailText, $detailImagePath, $enabled)) {
				throw new \RuntimeException('Не удалось сохранить изменения.');
			}

			$this->saveBlogSeo(BlogSeoService::TYPE_TOPIC, (string) $id);

			header('Location: /admin/content/blog/rubrics/' . $id . '/?saved=1');
			return;
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			if ($message === '') {
				$message = 'Не удалось сохранить изменения.';
			}

			header('Location: /admin/content/blog/rubrics/' . $id . '/?error=' . rawurlencode($message));
			return;
		}
	}

	public function delete(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			if (!(new BlogTopicsModel())->deleteById($id)) {
				throw new \RuntimeException('Не удалось удалить рубрику.');
			}

			$this->setFlash(true, 'Рубрика удалена.');
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->setFlash(false, $message !== '' ? $message : 'Не удалось удалить рубрику.');
		}

		header('Location: /admin/content/blog/rubrics/');
	}

	private function saveTopicImageUpload(int $topicId, string $fileKey, string $existingUrl, string $imageType = 'preview'): string
	{
		$file = $_FILES[$fileKey] ?? null;
		if (!is_array($file)) {
			return $existingUrl;
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode === UPLOAD_ERR_NO_FILE) {
			return $existingUrl;
		}

		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new \RuntimeException('Ошибка загрузки изображения.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new \RuntimeException('Некорректный загруженный файл.');
		}

		$mime = $this->detectImageMimeType($tmpPath);
		$allowedMimeToExt = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/webp' => 'webp',
		];

		if (!isset($allowedMimeToExt[$mime])) {
			throw new \RuntimeException('Разрешены только JPG/PNG/GIF/WEBP изображения.');
		}

		$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
		if ($documentRoot === '') {
			throw new \RuntimeException('Document root is not configured.');
		}

		$uploadDir = $documentRoot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'topics';
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
			throw new \RuntimeException('Не удалось создать папку загрузки.');
		}

		$safeImageType = $imageType === 'detail' ? 'detail' : 'preview';
		$fileName = sprintf(
			'blog_topic_%d_%s_%s_%s.%s',
			$topicId,
			$safeImageType,
			date('Ymd_His'),
			bin2hex(random_bytes(4)),
			$allowedMimeToExt[$mime]
		);
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new \RuntimeException('Не удалось сохранить изображение.');
		}

		return '/upload/images/blog/topics/' . $fileName;
	}

	private function saveArticleImageUpload(int $articleId, string $fileKey, string $existingUrl, string $imageType): string
	{
		$file = $_FILES[$fileKey] ?? null;
		if (!is_array($file)) {
			return $existingUrl;
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode === UPLOAD_ERR_NO_FILE) {
			return $existingUrl;
		}

		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new \RuntimeException('Ошибка загрузки изображения.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new \RuntimeException('Некорректный загруженный файл.');
		}

		$mime = $this->detectImageMimeType($tmpPath);
		$allowedMimeToExt = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/webp' => 'webp',
		];

		if (!isset($allowedMimeToExt[$mime])) {
			throw new \RuntimeException('Разрешены только JPG/PNG/GIF/WEBP изображения.');
		}

		$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
		if ($documentRoot === '') {
			throw new \RuntimeException('Document root is not configured.');
		}

		$uploadDir = $documentRoot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'articles';
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
			throw new \RuntimeException('Не удалось создать папку загрузки.');
		}

		$safeImageType = $imageType === 'detail' ? 'detail' : 'preview';
		$fileName = sprintf(
			'blog_article_%d_%s_%s_%s.%s',
			$articleId,
			$safeImageType,
			date('Ymd_His'),
			bin2hex(random_bytes(4)),
			$allowedMimeToExt[$mime]
		);
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new \RuntimeException('Не удалось сохранить изображение.');
		}

		return '/upload/images/blog/articles/' . $fileName;
	}

	private function normalizeArticleInput(?int $excludeArticleId = null): array
	{
		$topicIds = $this->normalizeTopicIds($_POST['topic_ids'] ?? []);
		$topicId = $topicIds[0] ?? 0;
		$title = trim((string) ($_POST['title'] ?? ''));
		$codeService = new SymbolicCodeService();
		$code = $codeService->normalize((string) ($_POST['code'] ?? ''));
		if ($code === '') {
			$code = $codeService->fromTitle($title);
		}
		$previewText = trim((string) ($_POST['preview_text'] ?? ''));
		$detailText = (string) ($_POST['detail_text'] ?? '');
		$author = trim((string) ($_POST['author'] ?? ''));

		if ($topicId <= 0) {
			return ['error' => 'Выберите рубрику статьи.'];
		}

		if ($title === '') {
			return ['error' => 'Введите название статьи.'];
		}

		if (!$codeService->isValid($code)) {
			return ['error' => 'Символьный код должен содержать только латинские буквы, цифры, "-" и "_".'];
		}

		if ((new BlogArticlesModel())->isCodeTaken($code, $excludeArticleId)) {
			return ['error' => 'Символьный код уже используется.'];
		}

		if (mb_strlen($previewText) > 500) {
			return ['error' => 'Preview текст должен быть не длиннее 500 символов.'];
		}

		return [
			'error' => '',
			'topic_id' => $topicId,
			'topic_ids' => $topicIds,
			'title' => $title,
			'code' => $code,
			'enabled' => isset($_POST['enabled']) ? 1 : 0,
			'preview_text' => $previewText,
			'detail_text' => $detailText,
			'author' => $author,
		];
	}

	private function normalizeTopicIds(mixed $rawTopicIds): array
	{
		if (!is_array($rawTopicIds)) {
			return [];
		}

		$result = [];
		foreach ($rawTopicIds as $topicId) {
			$topicId = (int) $topicId;
			if ($topicId > 0) {
				$result[] = $topicId;
			}
		}

		return array_values(array_unique($result));
	}

	private function loadTopics(): array
	{
		try {
			return (new BlogTopicsModel())->findAllWithArticleCounts(false);
		} catch (Throwable) {
			return [];
		}
	}

	/**
	 * @param array<int, object> $topics
	 * @return list<int>
	 */
	private function resolvePreselectedTopicIds(array $topics): array
	{
		$topicId = (int) ($_GET['topic_id'] ?? 0);
		if ($topicId <= 0) {
			return [];
		}

		foreach ($topics as $topic) {
			if ((int) ($topic->id ?? 0) === $topicId) {
				return [$topicId];
			}
		}

		return [];
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

	/**
	 * @return array{title: string, description: string, keywords: string, robots_index: bool, robots_follow: bool}
	 */
	private function emptySeoForm(): array
	{
		return [
			'title' => '',
			'description' => '',
			'keywords' => '',
			'robots_index' => true,
			'robots_follow' => true,
		];
	}

	private function saveBlogSeo(string $type, string $key): void
	{
		(new BlogSeoService())->saveFromAdminPost($type, $key, $_POST);
	}

	private function ensureAdmin(): bool
	{
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			header('Location: /admin/login/');
			return false;
		}

		return true;
	}

	private function setFlash(bool $success, string $message): void
	{
		$_SESSION[self::FLASH_KEY] = [
			'success' => $success,
			'message' => $message,
		];
	}

	private function resolvePublicationRedirect(int $articleId): string
	{
		$back = trim((string) ($_POST['back'] ?? ''));
		if ($back !== '' && str_starts_with($back, '/')) {
			return $back;
		}

		if ($articleId > 0) {
			return '/admin/content/blog/articles/' . $articleId . '/';
		}

		return '/admin/content/blog/articles/';
	}
}
