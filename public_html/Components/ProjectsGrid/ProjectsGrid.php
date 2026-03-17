<?php

namespace Components\ProjectsGrid;

use Models\ProjectsModel;
use Modules\Main\BaseComponent;

class ProjectsGrid extends BaseComponent
{
	protected function prepareData(array $params = []): void
	{
		$projectsModel = new ProjectsModel();
		$this->setParam('items', $projectsModel->findAll());
		$this->setParam('use_filters', false);

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