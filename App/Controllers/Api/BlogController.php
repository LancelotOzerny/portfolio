<?php

namespace Controllers\Api;

use App\Services\Blog\BlogApiService;
use Throwable;

class BlogController
{
	public function __construct(
		private readonly BlogApiService $blogApi = new BlogApiService(),
	) {
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET');
		header('Access-Control-Allow-Headers: *');
		header('Content-Type: application/json; charset=utf-8');
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
