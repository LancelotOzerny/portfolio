<?php

namespace Components\BlogArticlesCarousel;

use App\Services\Blog\BlogDateFormatter;
use Models\BlogArticlesModel;
use Modules\Main\BaseComponent;
use Throwable;

class BlogArticlesCarousel extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$limit = max(1, (int) ($params['limit'] ?? 6));
		$items = [];
		$error = '';

		try {
			$items = (new BlogArticlesModel())->findLatestActive($limit);
		} catch (Throwable $exception) {
			$error = $exception->getMessage();
		}

		$dateFormatter = new BlogDateFormatter();
		$mappedItems = [];

		foreach ($items as $item) {
			$articleId = (int) ($item->id ?? 0);
			$topicId = (int) ($item->topic_id_resolved ?? $item->topic_id ?? 0);
			if ($articleId <= 0 || $topicId <= 0) {
				continue;
			}

			$previewImage = trim((string) ($item->preview_image_path ?? ''));

			$mappedItems[] = [
				'id' => $articleId,
				'topic_id' => $topicId,
				'title' => (string) ($item->title ?? 'Без названия'),
				'preview' => (string) ($item->preview_text ?? ''),
				'image' => $previewImage !== '' ? $previewImage : '/Templates/Inner/img/no-image.webp',
				'date' => $dateFormatter->format((string) ($item->created_at ?? '')),
				'topic_title' => (string) ($item->topic_title ?? ''),
				'url' => '/blog/' . $topicId . '/' . $articleId . '/',
			];
		}

		$this->params = $params;
		$this->setParam('items', $mappedItems);
		$this->setParam('error', $error);
		$this->setParam('limit', $limit);
		$this->setParam('title', trim((string) ($params['title'] ?? 'Блог')) ?: 'Блог');
	}

	protected function getEditableParamKeys(): array
	{
		return ['limit', 'title'];
	}
}
