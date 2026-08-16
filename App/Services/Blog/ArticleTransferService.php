<?php

namespace App\Services\Blog;

use Models\BlogArticlesModel;
use Models\BlogTopicsModel;
use RuntimeException;
use Throwable;

class ArticleTransferService
{
	private const FORMAT_TYPE = 'lancy.studio.blog-article';
	private const FORMAT_VERSION = 1;
	private const MAX_UPLOAD_BYTES = 5242880;

	public function exportPayload(object $article): array
	{
		$articleId = (int) ($article->id ?? 0);

		return [
			'type' => self::FORMAT_TYPE,
			'version' => self::FORMAT_VERSION,
			'title' => (string) ($article->title ?? ''),
			'preview_text' => (string) ($article->preview_text ?? ''),
			'detail_text' => (string) ($article->detail_text ?? ''),
			'code' => (string) ($article->code ?? ''),
			'author' => (string) ($article->author ?? ''),
			'topic_codes' => $this->topicCodesByArticleId($articleId),
		];
	}

	public function exportJson(object $article): string
	{
		$encoded = json_encode(
			$this->exportPayload($article),
			JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
		);

		if (!is_string($encoded) || $encoded === '') {
			throw new RuntimeException('Не удалось сформировать файл экспорта.');
		}

		return $encoded;
	}

	public function exportFileName(object $article): string
	{
		$code = (new SymbolicCodeService())->normalize((string) ($article->code ?? ''));
		if ($code === '') {
			$code = 'article-' . (int) ($article->id ?? 0);
		}

		return $code . '.json';
	}

	public function importFromUpload(array $file): int
	{
		if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
			throw new RuntimeException('Выберите JSON-файл статьи.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new RuntimeException('Некорректный загруженный файл.');
		}

		$size = (int) ($file['size'] ?? 0);
		if ($size <= 0 || $size > self::MAX_UPLOAD_BYTES) {
			throw new RuntimeException('Файл слишком большой. Максимум 5 МБ.');
		}

		$contents = file_get_contents($tmpPath);
		if (!is_string($contents) || trim($contents) === '') {
			throw new RuntimeException('Файл экспорта пуст.');
		}

		return $this->importFromJson($contents);
	}

	public function importFromJson(string $json): int
	{
		$payload = json_decode($json, true);
		if (!is_array($payload) || ($payload['type'] ?? '') !== self::FORMAT_TYPE) {
			throw new RuntimeException('Это не файл экспорта статьи.');
		}

		$title = trim((string) ($payload['title'] ?? ''));
		if ($title === '') {
			throw new RuntimeException('В файле нет названия статьи.');
		}

		$previewText = trim((string) ($payload['preview_text'] ?? ''));
		if (mb_strlen($previewText) > 500) {
			$previewText = mb_substr($previewText, 0, 500);
		}

		$detailText = (new ArticleContentSanitizer())->sanitize((string) ($payload['detail_text'] ?? ''));
		$author = trim((string) ($payload['author'] ?? ''));
		$topicIds = $this->resolveTopicIds($payload['topic_codes'] ?? []);
		if ($topicIds === []) {
			throw new RuntimeException('Сначала создайте хотя бы одну рубрику.');
		}

		$model = new BlogArticlesModel();
		$code = $this->uniqueCode((string) ($payload['code'] ?? ''), $title, $model);
		$articleId = $model->createForAdmin($topicIds[0], $title, $code);
		if ($articleId <= 0) {
			throw new RuntimeException('Не удалось создать статью.');
		}

		if (!$model->updateEditorData(
			$articleId,
			$topicIds[0],
			$title,
			$code,
			0,
			$previewText,
			'',
			$detailText,
			'',
			$author
		)) {
			throw new RuntimeException('Статья создана, но данные не удалось сохранить.');
		}

		if (!$model->replaceTopicIds($articleId, $topicIds)) {
			throw new RuntimeException('Не удалось сохранить рубрики статьи.');
		}

		return $articleId;
	}

	private function uniqueCode(string $preferred, string $title, BlogArticlesModel $model): string
	{
		$codeService = new SymbolicCodeService();
		$base = $codeService->normalize($preferred);
		if ($base === '') {
			$base = $codeService->fromTitle($title);
		}
		if ($base === '' || !$codeService->isValid($base)) {
			$base = 'article';
		}

		$code = $base;
		$index = 2;
		while ($model->isCodeTaken($code)) {
			$code = $base . '-' . $index;
			$index++;
			if ($index > 1000) {
				throw new RuntimeException('Не удалось подобрать свободный символьный код.');
			}
		}

		return $code;
	}

	/**
	 * @param mixed $rawCodes
	 * @return list<int>
	 */
	private function resolveTopicIds(mixed $rawCodes): array
	{
		$topicsModel = new BlogTopicsModel();
		$ids = [];

		if (is_array($rawCodes)) {
			foreach ($rawCodes as $code) {
				$topic = $topicsModel->findByCode((string) $code);
				$topicId = (int) ($topic->id ?? 0);
				if ($topicId > 0) {
					$ids[] = $topicId;
				}
			}
		}

		$ids = array_values(array_unique($ids));
		if ($ids !== []) {
			return $ids;
		}

		try {
			$topics = $topicsModel->findAllWithArticleCounts(false);
		} catch (Throwable) {
			$topics = [];
		}

		$firstId = (int) (($topics[0] ?? null)->id ?? 0);

		return $firstId > 0 ? [$firstId] : [];
	}

	/**
	 * @return list<string>
	 */
	private function topicCodesByArticleId(int $articleId): array
	{
		if ($articleId <= 0) {
			return [];
		}

		try {
			$topicIds = (new BlogArticlesModel())->findTopicIdsByArticleId($articleId);
		} catch (Throwable) {
			return [];
		}

		$topicsModel = new BlogTopicsModel();
		$codes = [];
		foreach ($topicIds as $topicId) {
			try {
				$topic = $topicsModel->findById((int) $topicId);
			} catch (Throwable) {
				continue;
			}

			$code = trim((string) ($topic->code ?? ''));
			if ($code !== '') {
				$codes[] = $code;
			}
		}

		return array_values(array_unique($codes));
	}
}
