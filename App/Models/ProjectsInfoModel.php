<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class ProjectsInfoModel extends BaseModel
{
	protected string $table = 'projects_info';

	public function findAllByProjectId(int $projectId): array
	{
		return $this->findAllBy('project_id', $projectId);
	}

	public function replaceByProjectId(int $projectId, array $items): bool
	{
		$deleteQb = (new QueryBuilder($this->table))
			->delete()
			->where('project_id', '=', $projectId);

		if (!$this->execWriteQuery($deleteQb)) {
			throw new \RuntimeException('Unable to clear project info.');
		}

		if (empty($items)) {
			return true;
		}

		foreach ($items as $item) {
			$date = trim((string) ($item['date'] ?? ''));
			$developTime = trim((string) ($item['develop_time'] ?? ''));
			$version = trim((string) ($item['version'] ?? ''));

			if ($date === '' && $developTime === '' && $version === '') {
				continue;
			}

			$insertQb = (new QueryBuilder($this->table))->insert([
				'project_id' => $projectId,
				'date' => $date,
				'develop_time' => $developTime,
				'version' => $version,
			]);

			if (!$this->execWriteQuery($insertQb)) {
				throw new \RuntimeException('Unable to save project info.');
			}
		}

		return true;
	}
}
