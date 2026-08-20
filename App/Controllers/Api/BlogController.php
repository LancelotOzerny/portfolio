<?php

namespace Controllers\Api;

use App\Services\Blog\BlogApiService;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class BlogController
{
	public function __construct(
		private readonly BlogApiService $blogApi = new BlogApiService(),
	) {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
		header('Access-Control-Allow-Headers: Authorization, Content-Type');
		header('Content-Type: application/json; charset=utf-8');
	}

	public function options(string $topic = '', string $article = '', string $rubric = ''): void
	{
		http_response_code(204);
	}

	public function rubrics(): void
	{
		try {
			$this->respond(true, [
				'items' => $this->blogApi->listTopics(),
			]);
		} catch (Throwable) {
			http_response_code(500);
			$this->respond(false, ['message' => 'Не удалось загрузить рубрики.']);
		}
	}

	public function articles(): void
	{
		$rubric = trim((string) ($_GET['rubric'] ?? ''), " \t\n\r\0\x0B/");

		try {
			$items = $this->blogApi->listArticles($rubric === '' ? null : $rubric);
		} catch (Throwable) {
			http_response_code(500);
			$this->respond(false, ['message' => 'Не удалось загрузить статьи.']);
			return;
		}

		if ($items === null) {
			http_response_code(404);
			$this->respond(false, ['message' => 'Рубрика не найдена.']);
			return;
		}

		$this->respond(true, ['items' => $items]);
	}

	public function createArticle(string $rubric): void
	{
		if (!$this->requireManager()) {
			return;
		}

		$data = $this->requestPayload();

		try {
			$item = $this->blogApi->createArticle(
				$rubric,
				(string) ($data['title'] ?? ''),
				(string) ($data['code'] ?? ''),
				(string) ($data['preview_text'] ?? '')
			);
		} catch (InvalidArgumentException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return;
		} catch (RuntimeException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return;
		} catch (Throwable) {
			http_response_code(500);
			$this->respond(false, ['message' => 'Не удалось создать статью.']);
			return;
		}

		if ($item === null) {
			http_response_code(404);
			$this->respond(false, ['message' => 'Рубрика не найдена.']);
			return;
		}

		http_response_code(201);
		$this->respond(true, ['item' => $item]);
	}

	public function editSeo(string $article): void
	{
		if (!$this->requireManager()) {
			return;
		}

		try {
			$seo = $this->blogApi->updateArticleSeo($article, $this->requestPayload());
		} catch (InvalidArgumentException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return;
		} catch (RuntimeException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return;
		} catch (Throwable) {
			http_response_code(500);
			$this->respond(false, ['message' => 'Не удалось сохранить SEO.']);
			return;
		}

		if ($seo === null) {
			http_response_code(404);
			$this->respond(false, ['message' => 'Статья не найдена.']);
			return;
		}

		$this->respond(true, ['seo' => $seo]);
	}

	public function editPreviewText(string $article): void
	{
		if (!$this->requireManager()) {
			return;
		}

		$data = $this->requestPayload();

		try {
			$item = $this->blogApi->updateArticlePreviewText(
				$article,
				(string) ($data['preview_text'] ?? '')
			);
		} catch (InvalidArgumentException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return;
		} catch (RuntimeException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return;
		} catch (Throwable) {
			http_response_code(500);
			$this->respond(false, ['message' => 'Не удалось сохранить preview текст.']);
			return;
		}

		if ($item === null) {
			http_response_code(404);
			$this->respond(false, ['message' => 'Статья не найдена.']);
			return;
		}

		$this->respond(true, ['item' => $item]);
	}

	public function editDetailText(string $article): void
	{
		if (!$this->requireManager()) {
			return;
		}

		$data = $this->requestPayload();

		try {
			$item = $this->blogApi->updateArticleDetailText(
				$article,
				(string) ($data['detail_text'] ?? '')
			);
		} catch (InvalidArgumentException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return;
		} catch (RuntimeException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return;
		} catch (Throwable) {
			http_response_code(500);
			$this->respond(false, ['message' => 'Не удалось сохранить детальный текст.']);
			return;
		}

		if ($item === null) {
			http_response_code(404);
			$this->respond(false, ['message' => 'Статья не найдена.']);
			return;
		}

		$this->respond(true, ['item' => $item]);
	}

	public function editPreviewImage(string $article): void
	{
		$this->editArticleImage($article, 'preview');
	}

	public function editDetailImage(string $article): void
	{
		$this->editArticleImage($article, 'detail');
	}

	public function uploadMedia(): void
	{
		if (!$this->requireManager()) {
			return;
		}

		$articleCode = trim((string) ($_POST['article_code'] ?? ''));
		$type = strtolower(trim((string) ($_POST['type'] ?? '')));

		if ($articleCode === '') {
			http_response_code(400);
			$this->respond(false, ['message' => 'Укажите article_code.']);
			return;
		}

		if ($type !== 'preview' && $type !== 'detail') {
			http_response_code(400);
			$this->respond(false, ['message' => 'Поле type должно быть preview или detail.']);
			return;
		}

		$this->respondUploadedImage($this->uploadImage($articleCode, $type));
	}

	public function article(string $topic, string $article): void
	{
		try {
			$item = $this->blogApi->getArticle($topic, $article);
		} catch (Throwable) {
			http_response_code(500);
			$this->respond(false, ['message' => 'Не удалось загрузить статью.']);
			return;
		}

		if ($item === null) {
			http_response_code(404);
			$this->respond(false, ['message' => 'Статья не найдена.']);
			return;
		}

		$this->respond(true, ['item' => $item]);
	}

	private function editArticleImage(string $article, string $type): void
	{
		if (!$this->requireManager()) {
			return;
		}

		$this->respondUploadedImage($this->uploadImage($article, $type));
	}

	private function uploadImage(string $article, string $type): ?string
	{
		try {
			return $this->blogApi->uploadArticleMedia($article, $type);
		} catch (InvalidArgumentException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return '';
		} catch (RuntimeException $exception) {
			http_response_code(400);
			$this->respond(false, ['message' => $exception->getMessage()]);
			return '';
		} catch (Throwable) {
			http_response_code(500);
			$this->respond(false, ['message' => 'Не удалось загрузить изображение.']);
			return '';
		}
	}

	private function respondUploadedImage(?string $url): void
	{
		if ($url === '') {
			return;
		}

		if ($url === null) {
			http_response_code(404);
			$this->respond(false, ['message' => 'Статья не найдена.']);
			return;
		}

		$this->respond(true, ['url' => $url]);
	}

	private function requireManager(): bool
	{
		if (!$this->blogApi->isAuthenticated()) {
			http_response_code(401);
			$this->respond(false, ['message' => 'Требуется авторизация.']);
			return false;
		}

		if (!$this->blogApi->canManage()) {
			http_response_code(403);
			$this->respond(false, ['message' => 'Недостаточно прав.']);
			return false;
		}

		return true;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function requestPayload(): array
	{
		if ($_POST !== []) {
			return $_POST;
		}

		$raw = file_get_contents('php://input');
		if (!is_string($raw) || trim($raw) === '') {
			return [];
		}

		$decoded = json_decode($raw, true);

		return is_array($decoded) ? $decoded : [];
	}

	/**
	 * @param array<string, mixed> $payload
	 */
	private function respond(bool $success, array $payload): void
	{
		echo json_encode(['success' => $success] + $payload, JSON_UNESCAPED_UNICODE);
	}
}
