<?php

namespace Components\ProjectsGrid;

use App\Services\TextTruncator;
use Models\ProjectsModel;
use Modules\Main\BaseComponent;

class ProjectsGrid extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$limit = $params['limit'] ?? 0;
		$excludeId = (int) ($params['exclude_id'] ?? 0);
		$useRandom = (bool) ($params['random'] ?? false);

		$projectsModel = new ProjectsModel();
		$items = $projectsModel->findAll($useRandom || $excludeId > 0 ? 0 : $limit);

		if ($excludeId > 0)
		{
			$items = array_values(array_filter($items, static function ($item) use ($excludeId): bool {
				return (int) ($item->id ?? 0) !== $excludeId;
			}));
		}

		if ($useRandom)
		{
			shuffle($items);
		}

		if ($limit > 0 && ($useRandom || $excludeId > 0))
		{
			$items = array_slice($items, 0, $limit);
		}

		$truncator = new TextTruncator();
		foreach ($items as $item) {
			$item->preview_text = $truncator->truncate((string) ($item->preview_text ?? ''));
		}

		$this->setParam('items', $items);
		$this->setParam('use_filters', false);
		$this->setParam('limit', $limit);
		$this->setParam('exclude_id', $excludeId);
		$this->setParam('random', $useRandom);

		if ($params['use_filters'] ?? null)
		{
			$filters = [
				0 => 'Все проекты'
			];
			foreach($this->getParam('items') ?? [] as $item)
			{
				foreach ($item->tags as $tag)
				{
					if ($tag->use_as_filter)
					{
						$filters[$tag->id] = $tag->name;
					}
				}
			}

			$this->setParam('use_filters', true);
			$this->setParam('filters', $filters);
		}
	}
}
