<?php

namespace Components\BlogSections;

use Models\BlogTopicsModel;
use Modules\Main\BaseComponent;
use Throwable;

class BlogSections extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$items = [];
		$error = '';

		try {
			$items = (new BlogTopicsModel())->findAllWithArticleCounts((bool) ($params['only_enabled'] ?? true));
		} catch (Throwable $exception) {
			$error = $exception->getMessage();
		}

		$this->setParam('items', $items);
		$this->setParam('error', $error);
		$this->setParam('is_admin', (bool) ($params['is_admin'] ?? false));
		$this->setParam('flash', $params['flash'] ?? null);
	}
}
