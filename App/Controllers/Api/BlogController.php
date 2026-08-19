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

	public function options(string $topic = '', string $article = ''): void
	{
		http_response_code(204);
	}

	public function list(): void
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

	public function topic(string $topic): void
	{
		try {
			$items = $this->blogApi->listTopicArticles($topic);
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

	public function uploadMedia(): void
	{
		if (!$this->blogApi->isAuthenticated()) {
			http_response_code(401);
			$this->respond(false, ['message' => 'Требуется авторизация.']);
			return;
		}

		if (!$this->blogApi->canManageMedia()) {
			http_response_code(403);
			$this->respond(false, ['message' => 'Недостаточно прав.']);
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

		try {
			$url = $this->blogApi->uploadArticleMedia($articleCode, $type);
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
			$this->respond(false, ['message' => 'Не удалось загрузить изображение.']);
			return;
		}

		if ($url === null) {
			http_response_code(404);
			$this->respond(false, ['message' => 'Статья не найдена.']);
			return;
		}

		$this->respond(true, ['url' => $url]);
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

	/**
	 * @param array<string, mixed> $payload
	 */
	private function respond(bool $success, array $payload): void
	{
		echo json_encode(['success' => $success] + $payload, JSON_UNESCAPED_UNICODE);
	}
}
