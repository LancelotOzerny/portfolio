<?php

namespace Models;

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
		$deleteStmt = $this->db->prepare("DELETE FROM {$this->table} WHERE project_id = :project_id");
		if (!$deleteStmt->execute([':project_id' => $projectId])) {
			throw new \RuntimeException('Unable to clear project links.');
		}

		if (empty($links)) {
			return true;
		}

		$insertStmt = $this->db->prepare("INSERT INTO {$this->table} (project_id, name, link) VALUES (:project_id, :name, :link)");

		foreach ($links as $row) {
			$name = trim((string) ($row['name'] ?? ''));
			$link = trim((string) ($row['link'] ?? ''));

			if ($name === '' && $link === '') {
				continue;
			}

			if (!$insertStmt->execute([
				':project_id' => $projectId,
				':name' => $name,
				':link' => $link,
			])) {
				throw new \RuntimeException('Unable to save project links.');
			}
		}

		return true;
	}
}
