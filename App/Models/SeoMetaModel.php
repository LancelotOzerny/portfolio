<?php

namespace Models;

use Modules\DBWork\QueryBuilder;
use Modules\Main\BaseModel;

class SeoMetaModel extends BaseModel
{
	protected string $table = 'seo_meta';

	public function findByTarget(string $type, string $key): ?object
	{
		$qb = (new QueryBuilder($this->table))
			->select()
			->where('target_type', '=', $type)
			->where('target_key', '=', $key);

		return $this->execQuery($qb, true);
	}

	public function saveByTarget(string $type, string $key, array $data): bool
	{
		$existing = $this->findByTarget($type, $key);
		$now = date('Y-m-d H:i:s');
		$payload = [
			'title' => $data['title'] ?? null,
			'description' => $data['description'] ?? null,
			'canonical_url' => $data['canonical_url'] ?? null,
			'robots_index' => (int) ($data['robots_index'] ?? 1),
			'robots_follow' => (int) ($data['robots_follow'] ?? 1),
			'og_title' => $data['og_title'] ?? null,
			'og_description' => $data['og_description'] ?? null,
			'og_image' => $data['og_image'] ?? null,
			'updated_at' => $now,
		];

		if ($existing === null) {
			$payload['target_type'] = $type;
			$payload['target_key'] = $key;
			$payload['created_at'] = $now;

			$qb = (new QueryBuilder($this->table))->insert($payload);
			return $this->execInsertQuery($qb) > 0;
		}

		$qb = (new QueryBuilder($this->table))
			->update($payload)
			->where('target_type', '=', $type)
			->where('target_key', '=', $key);

		return $this->execWriteQuery($qb);
	}

	public function deleteByTarget(string $type, string $key): bool
	{
		$qb = (new QueryBuilder($this->table))
			->delete()
			->where('target_type', '=', $type)
			->where('target_key', '=', $key);

		return $this->execWriteQuery($qb);
	}
}
