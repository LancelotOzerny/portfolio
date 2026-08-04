<?php

namespace Components\ProjectsGrid;

use App\Services\TextTruncator;
use Models\ProjectsModel;
use Modules\Main\BaseComponent;

class ProjectsGrid extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$limit = (int) ($params['limit'] ?? 0);
		$excludeId = (int) ($params['exclude_id'] ?? 0);
		$useRandom = $this->normalizeBool($params['random'] ?? false, false);
		$useFilters = $this->normalizeBool($params['use_filters'] ?? false, false);
		$showTags = $this->normalizeBool($params['show_tags'] ?? true, true);

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
		$this->setParam('use_filters', $useFilters);
		$this->setParam('limit', $limit);
		$this->setParam('exclude_id', $excludeId);
		$this->setParam('random', $useRandom);
		$this->setParam('show_tags', $showTags);

		if ($useFilters)
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

			$this->setParam('filters', $filters);
		}
	}

	private function normalizeBool(mixed $value, bool $default): bool
	{
		if (is_bool($value)) {
			return $value;
		}

		if ($value === null || $value === '') {
			return $default;
		}

		if (is_int($value) || is_float($value)) {
			return (int) $value !== 0;
		}

		if (is_string($value)) {
			$normalized = strtolower(trim($value));

			if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
				return false;
			}

			if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
				return true;
			}
		}

		return $default;
	}
}
