<?php

namespace Models;

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
		$deleteStmt = $this->db->prepare("DELETE FROM {$this->table} WHERE project_id = :project_id");
		if (!$deleteStmt->execute([':project_id' => $projectId])) {
			throw new \RuntimeException('Unable to clear project info.');
		}

		if (empty($items)) {
			return true;
		}

		$insertStmt = $this->db->prepare(
			"INSERT INTO {$this->table} (project_id, date, develop_time, version) VALUES (:project_id, :date, :develop_time, :version)"
		);

		foreach ($items as $item) {
			$date = trim((string) ($item['date'] ?? ''));
			$developTime = trim((string) ($item['develop_time'] ?? ''));
			$version = trim((string) ($item['version'] ?? ''));

			if ($date === '' && $developTime === '' && $version === '') {
				continue;
			}

			if (!$insertStmt->execute([
				':project_id' => $projectId,
				':date' => $date,
				':develop_time' => $developTime,
				':version' => $version,
			])) {
				throw new \RuntimeException('Unable to save project info.');
			}
		}

		return true;
	}
}
