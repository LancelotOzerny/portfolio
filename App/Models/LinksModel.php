<?php

namespace Models;

use Modules\Main\BaseModel;

class LinksModel extends BaseModel
{
	protected string $table = 'projects_links';

	public function findAllByProject(int $id) : array
	{
		$result = $this->findAllBy('project_id', $id);

		if (!is_array($result))
		{
			$result = [$result];
		}

		return $result ?: [];
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
