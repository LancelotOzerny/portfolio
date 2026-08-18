<?php
namespace Controllers\Public;

use App\Services\Blog\ArticleCommentService;
use App\Services\Blog\ArticleContentSanitizer;
use App\Services\Blog\ArticleRatingService;
use App\Services\Blog\ArticleViewCounter;
use App\Services\Blog\BlogArticlePublicationService;
use App\Services\Blog\BlogDateFormatter;
use App\Services\Blog\BlogSeoService;
use App\Services\Blog\SymbolicCodeService;
use App\Services\ContentEditor\ContentEditorUploadService;
use App\Services\Site\EditModeService;
use App\Services\Seo\SeoContext;
use App\Services\Seo\SeoValidator;
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
		$topicId = (int) ($topicData['id'] ?? 0);
		$path = '/blog/' . $topic . '/';

		if ($topicData !== null && $topicId > 0) {
			$this->setSeo(SeoContext::entity(
				'blog_topic',
				(string) $topicId,
				[
					'title' => $topicData['name'] ?? 'Тема не найдена',
					'description' => $topicData['preview_text'] ?? ($topicData['description'] ?? 'Тестовая тема блога.'),
					'og_title' => $topicData['name'] ?? '',
					'og_description' => $topicData['preview_text'] ?? ($topicData['description'] ?? ''),
					'og_image' => $topicData['image'] ?? ($topicData['preview_image'] ?? ''),
					'robots_index' => true,
				],
				$path
			));
		} else {
			$this->setSeo(SeoContext::custom($path, [
				'title' => $topicData['name'] ?? 'Тема не найдена',
				'description' => $topicData['description'] ?? 'Тестовая тема блога.',
				'robots_index' => false,
			]));
		}

		Template::getInstance()->setParam('title', $topicData['name'] ?? 'Тема не найдена');
		Template::getInstance()->setParam('subtitle', 'Статьи выбранной темы');
		Template::getInstance()->setParam('show_contact_cta', false);

		$editMode = $this->isEditMode();
		$seoForm = ($editMode && $topicId > 0)
			? (new BlogSeoService())->getFormData(BlogSeoService::TYPE_TOPIC, (string) $topicId)
			: ['title' => '', 'description' => '', 'keywords' => ''];

		Template::getInstance()->showHeader();
		$this->render('topic', [
			'topic' => $topicData,
			'is_admin' => Auth::getInstance()->isAdmin(),
			'edit_mode' => $editMode,
			'csrf_token' => (new CsrfService())->getToken(),
			'save_success' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'save_error' => isset($_GET['error']) ? (string) $_GET['error'] : '',
			'seo_form' => $seoForm,
		]);
		Template::getInstance()->showFooter();
	}

	public function detail(string $topic, string $article): void
	{
		$topicData = $this->findTopic($topic);
		$articleData = $topicData !== null ? $this->findArticle($topicData, $article) : null;
		$rating = [
			'average' => 0.0,
			'count' => 0,
			'user_rating' => null,
			'can_vote' => false,
		];
		$comments = [];

		if ($articleData !== null && isset($articleData['id'])) {
			$articleId = (int) $articleData['id'];
			if ((new ArticleViewCounter())->registerIfUnique($articleId)) {
				$articleData['views_count'] = (int) ($articleData['views_count'] ?? 0) + 1;
			}

			$rating = (new ArticleRatingService())->getState($articleId);
			$articleData['rating'] = $rating['average'];
			$comments = (new ArticleCommentService())->getTreeForArticle($articleId);
			$articleData['comments'] = $comments;
		}

		$isArticleEnabled = (bool) ($articleData['enabled'] ?? true);
		$articleId = (int) ($articleData['id'] ?? 0);
		$path = '/blog/' . $topic . '/' . $article . '/';

		if ($articleData !== null && $articleId > 0) {
			$this->setSeo(SeoContext::entity(
				'blog_article',
				(string) $articleId,
				[
					'title' => $articleData['title'] ?? 'Статья не найдена',
					'description' => $articleData['preview'] ?? 'Тестовая статья блога.',
					'og_title' => $articleData['title'] ?? '',
					'og_description' => $articleData['preview'] ?? '',
					'og_image' => $articleData['preview_image_path'] ?? '',
					'robots_index' => $isArticleEnabled,
				],
				$path
			));
		} else {
			$this->setSeo(SeoContext::custom($path, [
				'title' => $articleData['title'] ?? 'Статья не найдена',
				'description' => $articleData['preview'] ?? 'Тестовая статья блога.',
				'robots_index' => false,
			]));
		}

		Template::getInstance()->setParam('title', $articleData['title'] ?? 'Статья не найдена');
		Template::getInstance()->setParam('subtitle', 'Детальная страница статьи');
		Template::getInstance()->setParam('show_contact_cta', false);

		$currentUser = Auth::getInstance()->getCurrentUser();
		$defaultAuthorName = $currentUser !== null
			? trim((string) ($currentUser->login ?? ''))
			: '';

		$comments = $comments !== []
			? $comments
			: (is_array($articleData['comments'] ?? null) ? $articleData['comments'] : []);

		$editMode = $this->isEditMode();
		$seoForm = ($editMode && $articleId > 0)
			? (new BlogSeoService())->getFormData(BlogSeoService::TYPE_ARTICLE, (string) $articleId)
			: ['title' => '', 'description' => '', 'keywords' => ''];

		$flash = $_SESSION['admin_blog_flash'] ?? null;
		unset($_SESSION['admin_blog_flash']);

		Template::getInstance()->showHeader();
		$this->render('detail', [
			'topic' => $topicData,
			'article' => $articleData,
			'rating' => $rating,
			'comments' => $comments,
			'default_author_name' => $defaultAuthorName,
			'is_admin' => Auth::getInstance()->isAdmin(),
			'edit_mode' => $editMode,
			'csrf_token' => (new CsrfService())->getToken(),
			'save_success' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'save_error' => isset($_GET['error']) ? (string) $_GET['error'] : '',
			'flash' => is_array($flash) ? $flash : null,
			'seo_form' => $seoForm,
		]);
		Template::getInstance()->showFooter();
	}

	public function rate(string $topic, string $article): void
	{
		header('Content-Type: application/json; charset=utf-8');

		$topicData = $this->findTopic($topic);
		$articleData = $topicData !== null ? $this->findArticle($topicData, $article) : null;
		$articleId = (int) ($articleData['id'] ?? 0);

		if ($articleId <= 0) {
			http_response_code(404);
			echo json_encode([
				'success' => false,
				'message' => 'Статья не найдена.',
			], JSON_UNESCAPED_UNICODE);
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			http_response_code(403);
			echo json_encode([
				'success' => false,
				'message' => 'Недействительный CSRF-токен.',
			], JSON_UNESCAPED_UNICODE);
			return;
		}

		$rating = (int) ($_POST['rating'] ?? 0);
		$result = (new ArticleRatingService())->vote($articleId, $rating);

		if (!$result['success']) {
			http_response_code(400);
		}

		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function commentStore(string $topic, string $article): void
	{
		header('Content-Type: application/json; charset=utf-8');

		$topicData = $this->findTopic($topic);
		$articleData = $topicData !== null ? $this->findArticle($topicData, $article) : null;
		$articleId = (int) ($articleData['id'] ?? 0);

		if ($articleId <= 0) {
			http_response_code(404);
			echo json_encode([
				'success' => false,
				'message' => 'Статья не найдена.',
			], JSON_UNESCAPED_UNICODE);
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			http_response_code(403);
			echo json_encode([
				'success' => false,
				'message' => 'Недействительный CSRF-токен.',
			], JSON_UNESCAPED_UNICODE);
			return;
		}

		$parentId = (int) ($_POST['parent_id'] ?? 0);
		$currentUser = Auth::getInstance()->getCurrentUser();
		$authorName = $currentUser !== null
			? trim((string) ($currentUser->login ?? ''))
			: 'Аноним';
		if ($authorName === '') {
			$authorName = 'Аноним';
		}

		$result = (new ArticleCommentService())->addComment(
			$articleId,
			$authorName,
			(string) ($_POST['comment'] ?? ''),
			$parentId > 0 ? $parentId : null
		);

		if (!$result['success']) {
			http_response_code(400);
		}

		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function commentVote(string $topic, string $article): void
	{
		header('Content-Type: application/json; charset=utf-8');

		$topicData = $this->findTopic($topic);
		$articleData = $topicData !== null ? $this->findArticle($topicData, $article) : null;
		$articleId = (int) ($articleData['id'] ?? 0);

		if ($articleId <= 0) {
			http_response_code(404);
			echo json_encode([
				'success' => false,
				'message' => 'Статья не найдена.',
			], JSON_UNESCAPED_UNICODE);
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			http_response_code(403);
			echo json_encode([
				'success' => false,
				'message' => 'Недействительный CSRF-токен.',
			], JSON_UNESCAPED_UNICODE);
			return;
		}

		$result = (new ArticleCommentService())->vote(
			$articleId,
			(int) ($_POST['comment_id'] ?? 0),
			(int) ($_POST['vote'] ?? 0)
		);

		if (!$result['success']) {
			http_response_code(400);
		}

		echo json_encode($result, JSON_UNESCAPED_UNICODE);
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

		$model = new BlogArticlesModel();
		$dbArticle = $this->resolveDbArticle($article);

		if ($dbArticle === null) {
			$this->redirectToArticle($topic, $article, 'Article not found.');
			return;
		}

		$articleId = (int) ($dbArticle->id ?? 0);
		$detailText = (new ArticleContentSanitizer())->sanitize((string) ($_POST['detail_text'] ?? ''));

		try {
			$topicIds = $model->findTopicIdsByArticleId($articleId);
		} catch (Throwable) {
			$topicIds = [];
		}

		$topicId = (int) ($topicIds[0] ?? (int) ($dbArticle->topic_id ?? 0));
		if ($topicId <= 0) {
			$this->redirectToArticle($topic, $article, 'Article topic was not found.');
			return;
		}

		$codeService = new SymbolicCodeService();
		$articleCode = $codeService->resolvePublicSegment(
			(string) ($dbArticle->code ?? ''),
			$articleId
		);

		try {
			if (!$model->updateEditorData(
				$articleId,
				$topicId,
				(string) ($dbArticle->title ?? ''),
				(string) ($dbArticle->code ?? $articleCode),
				(int) ($dbArticle->enabled ?? 0),
				(string) ($dbArticle->preview_text ?? ''),
				(string) ($dbArticle->preview_image_path ?? ''),
				$detailText,
				(string) ($dbArticle->detail_image_path ?? ''),
				(string) ($dbArticle->author ?? '')
			)) {
				throw new \RuntimeException('Unable to save article.');
			}
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->redirectToArticle($topic, $article, $message !== '' ? $message : 'Unable to save article.');
			return;
		}

		header('Location: /blog/' . rawurlencode($topic) . '/' . rawurlencode($articleCode) . '/?edit=true&saved=1');
	}

	public function updateTopicBasic(string $topic): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			$this->redirectToTopic($topic, 'Недействительный CSRF-токен.');
			return;
		}

		$dbTopic = $this->resolveDbTopic($topic);
		if ($dbTopic === null) {
			$this->redirectToTopic($topic, 'Рубрика не найдена.');
			return;
		}

		$title = trim((string) ($_POST['title'] ?? ''));
		$description = trim((string) ($_POST['description'] ?? ''));

		if ($title === '') {
			$this->redirectToTopic($topic, 'Укажите название.');
			return;
		}

		if (mb_strlen($title) > 255 || mb_strlen($description) > 500) {
			$this->redirectToTopic($topic, 'Превышена максимальная длина поля.');
			return;
		}

		$usesDetailText = trim((string) ($dbTopic->detail_text ?? '')) !== '';
		$previewText = $usesDetailText ? (string) ($dbTopic->preview_text ?? '') : $description;
		$detailText = $usesDetailText ? $description : (string) ($dbTopic->detail_text ?? '');

		try {
			if (!(new BlogTopicsModel())->updateBasicInfo(
				(int) $dbTopic->id,
				$title,
				$previewText,
				$detailText
			)) {
				throw new \RuntimeException('Не удалось сохранить рубрику.');
			}
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->redirectToTopic($topic, $message !== '' ? $message : 'Не удалось сохранить рубрику.');
			return;
		}

		$codeService = new SymbolicCodeService();
		$topicCode = $codeService->resolvePublicSegment(
			(string) ($dbTopic->code ?? ''),
			(int) $dbTopic->id
		);

		header('Location: /blog/' . rawurlencode($topicCode) . '/?edit=true&saved=1');
	}

	public function updateTopicSeo(string $topic): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			$this->redirectToTopic($topic, 'Недействительный CSRF-токен.');
			return;
		}

		$dbTopic = $this->resolveDbTopic($topic);
		if ($dbTopic === null) {
			$this->redirectToTopic($topic, 'Рубрика не найдена.');
			return;
		}

		try {
			$fields = (new SeoValidator())->validateBlogSeoForm($_POST);
			(new BlogSeoService())->saveFromPublicFields(
				BlogSeoService::TYPE_TOPIC,
				(string) ((int) $dbTopic->id),
				$fields
			);
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->redirectToTopic($topic, $message !== '' ? $message : 'Не удалось сохранить SEO.');
			return;
		}

		$codeService = new SymbolicCodeService();
		$topicCode = $codeService->resolvePublicSegment(
			(string) ($dbTopic->code ?? ''),
			(int) $dbTopic->id
		);

		header('Location: /blog/' . rawurlencode($topicCode) . '/?edit=true&saved=1');
	}

	public function updateArticleBasic(string $topic, string $article): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			$this->redirectToArticle($topic, $article, 'Недействительный CSRF-токен.');
			return;
		}

		$dbArticle = $this->resolveDbArticle($article);
		if ($dbArticle === null) {
			$this->redirectToArticle($topic, $article, 'Статья не найдена.');
			return;
		}

		$title = trim((string) ($_POST['title'] ?? ''));
		$description = trim((string) ($_POST['description'] ?? ''));

		if ($title === '') {
			$this->redirectToArticle($topic, $article, 'Укажите название.');
			return;
		}

		if (mb_strlen($title) > 255 || mb_strlen($description) > 500) {
			$this->redirectToArticle($topic, $article, 'Превышена максимальная длина поля.');
			return;
		}

		try {
			if (!(new BlogArticlesModel())->updateBasicInfo(
				(int) $dbArticle->id,
				$title,
				$description
			)) {
				throw new \RuntimeException('Не удалось сохранить статью.');
			}
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->redirectToArticle($topic, $article, $message !== '' ? $message : 'Не удалось сохранить статью.');
			return;
		}

		$codeService = new SymbolicCodeService();
		$articleCode = $codeService->resolvePublicSegment(
			(string) ($dbArticle->code ?? ''),
			(int) $dbArticle->id
		);

		header('Location: /blog/' . rawurlencode($topic) . '/' . rawurlencode($articleCode) . '/?edit=true&saved=1');
	}

	public function updateArticleSeo(string $topic, string $article): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			$this->redirectToArticle($topic, $article, 'Недействительный CSRF-токен.');
			return;
		}

		$dbArticle = $this->resolveDbArticle($article);
		if ($dbArticle === null) {
			$this->redirectToArticle($topic, $article, 'Статья не найдена.');
			return;
		}

		try {
			$fields = (new SeoValidator())->validateBlogSeoForm($_POST);
			(new BlogSeoService())->saveFromPublicFields(
				BlogSeoService::TYPE_ARTICLE,
				(string) ((int) $dbArticle->id),
				$fields
			);
		} catch (Throwable $e) {
			$message = trim($e->getMessage());
			$this->redirectToArticle($topic, $article, $message !== '' ? $message : 'Не удалось сохранить SEO.');
			return;
		}

		$codeService = new SymbolicCodeService();
		$articleCode = $codeService->resolvePublicSegment(
			(string) ($dbArticle->code ?? ''),
			(int) $dbArticle->id
		);

		header('Location: /blog/' . rawurlencode($topic) . '/' . rawurlencode($articleCode) . '/?edit=true&saved=1');
	}

	public function uploadDetailImage(string $topic, string $article): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!$this->ensureUploadRequestIsAllowed($article)) {
			return;
		}

		try {
			$dbArticle = $this->resolveDbArticle($article);
			$articleId = (int) ($dbArticle->id ?? 0);
			$url = (new ContentEditorUploadService())->saveImage($articleId, 'image', 'articles', 'article');
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
			$dbArticle = $this->resolveDbArticle($article);
			$articleId = (int) ($dbArticle->id ?? 0);
			$file = (new ContentEditorUploadService())->saveFile($articleId, 'file', 'articles', 'article');
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
		$codeService = new SymbolicCodeService();
		$topicsModel = new BlogTopicsModel();
		$topic = null;

		try {
			if (ctype_digit($slug)) {
				$topic = $topicsModel->findById((int) $slug);
				if ($topic === null) {
					$topic = $topicsModel->findByCode($slug);
				}
			} else {
				$topic = $topicsModel->findByCode($slug);
			}
		} catch (Throwable) {
			$topic = null;
		}

		if ($topic !== null) {
			$topicId = (int) ($topic->id ?? 0);

			try {
				$articles = (new BlogArticlesModel())->findByTopicId($topicId, !$this->isAdmin());
			} catch (Throwable) {
				$articles = [];
			}

			return [
				'id' => $topicId,
				'name' => (string) ($topic->title ?? 'Без названия'),
				'slug' => $codeService->resolvePublicSegment((string) ($topic->code ?? ''), $topicId),
				'code' => (string) ($topic->code ?? ''),
				'preview_text' => (string) ($topic->preview_text ?? ''),
				'image' => (string) ($topic->image_path ?? ''),
				'description' => trim((string) ($topic->detail_text ?? '')) !== ''
					? (string) ($topic->detail_text ?? '')
					: (string) ($topic->preview_text ?? ''),
				'detail_image_path' => (string) ($topic->detail_image_path ?? ''),
				'articles' => $this->mapDbArticles($articles),
			];
		}

		foreach ($this->getTopics() as $demoTopic) {
			if ($demoTopic['slug'] === $slug) {
				return $demoTopic;
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
		$publicationService = new BlogArticlePublicationService();
		$codeService = new SymbolicCodeService();
		$articleIds = [];

		foreach ($articles as $article) {
			$articleId = (int) ($article->id ?? 0);
			if ($articleId > 0) {
				$articleIds[] = $articleId;
			}
		}

		$ratingSummaries = [];
		try {
			$ratingSummaries = (new BlogArticlesModel())->getRatingSummariesByArticleIds($articleIds);
		} catch (Throwable) {
			$ratingSummaries = [];
		}

		foreach ($articles as $article) {
			$articleId = (int) ($article->id ?? 0);
			if ($articleId <= 0) {
				continue;
			}

			$code = (string) ($article->code ?? '');
			$ratingSummary = $ratingSummaries[$articleId] ?? [
				'average' => 0.0,
				'count' => 0,
			];

			$result[] = [
				'id' => $articleId,
				'topic_id' => (int) ($article->topic_id ?? 0),
				'title' => (string) ($article->title ?? 'Без названия'),
				'slug' => $codeService->resolvePublicSegment($code, $articleId),
				'code' => $code,
				'enabled' => (int) ($article->enabled ?? 0) === 1,
				'detail_image' => trim((string) ($article->detail_image_path ?? '')) !== ''
					? (string) ($article->detail_image_path ?? '')
					: '/Templates/Inner/img/no-image.webp',
				'image' => trim((string) ($article->preview_image_path ?? '')) !== ''
					? (string) ($article->preview_image_path ?? '')
					: '/Templates/Inner/img/no-image.webp',
				'preview_image_path' => (string) ($article->preview_image_path ?? ''),
				'date' => $dateFormatter->format((string) ($publicationService->getPublicationDatetime($article) ?? '')),
				'rating' => (float) ($ratingSummary['average'] ?? 0),
				'rating_count' => (int) ($ratingSummary['count'] ?? 0),
				'views_count' => (int) ($article->views_count ?? 0),
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
			if (($article['slug'] ?? '') === $slug) {
				return $article;
			}

			if (ctype_digit($slug) && (string) ($article['id'] ?? '') === $slug) {
				return $article;
			}

			if (($article['code'] ?? '') !== '' && ($article['code'] ?? '') === $slug) {
				return $article;
			}
		}

		return null;
	}

	private function resolveDbArticle(string $slug): ?object
	{
		$model = new BlogArticlesModel();

		try {
			if (ctype_digit($slug)) {
				$article = $model->findById((int) $slug);
				if ($article !== null) {
					return $article;
				}
			}

			return $model->findByCode($slug);
		} catch (Throwable) {
			return null;
		}
	}

	private function resolveDbTopic(string $slug): ?object
	{
		$model = new BlogTopicsModel();

		try {
			if (ctype_digit($slug)) {
				$topic = $model->findById((int) $slug);
				if ($topic !== null) {
					return $topic;
				}
			}

			return $model->findByCode($slug);
		} catch (Throwable) {
			return null;
		}
	}

	private function redirectToTopic(string $topic, string $error): void
	{
		header('Location: /blog/' . rawurlencode($topic) . '/?edit=true&error=' . rawurlencode($error));
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

		if ($this->resolveDbArticle($article) === null) {
			http_response_code(400);
			echo json_encode(['success' => 0, 'error' => 'Invalid article.']);
			return false;
		}

		return true;
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
