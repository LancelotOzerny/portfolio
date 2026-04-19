<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class ProjectsLinkModel extends BaseModel
{
	protected string $table = 'projects_link';

	public function findAllByProjectId(int $projectId): array
	{
		return $this->findAllBy('project_id', $projectId);
	}

	public function replaceProjectLinks(int $projectId, array $links): bool
	{
		$deleteQb = (new QueryBuilder($this->table))
			->delete()
			->where('project_id', '=', $projectId);

		if (!$this->execWriteQuery($deleteQb)) {
			throw new \RuntimeException('Unable to clear project links.');
		}

		if (empty($links)) {
			return true;
		}

		foreach ($links as $row) {
			$name = trim((string) ($row['name'] ?? ''));
			$link = trim((string) ($row['link'] ?? ''));

			if ($name === '' && $link === '') {
				continue;
			}

			$insertQb = (new QueryBuilder($this->table))->insert([
				'project_id' => $projectId,
				'name' => $name,
				'link' => $link,
			]);

			if (!$this->execWriteQuery($insertQb)) {
				throw new \RuntimeException('Unable to save project links.');
			}
		}

		return true;
	}
}
