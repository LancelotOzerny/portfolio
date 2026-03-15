<?php

namespace Modules\Main;

use Modules\DBWork\QueryBuilder;
use Modules\DBWork\DBConnection;
use PDO;

abstract class BaseModel {
	protected string $table;
	protected array $fillable = [];
	protected \PDO | null $db = null;

	public function __construct()
	{
		$this->db = DBConnection::getConnection();
		$this->table = $this->table ?: (new \ReflectionClass($this))->getShortName();
	}

	public function findAll($limit = 0): array
	{
		$builder = new QueryBuilder($this->table);
		$builder->select();
		if ($limit > 0)
		{
			$builder->limit($limit);
		}

		return $this->execQuery($builder);
	}

	protected function findBy(string $column, $value, $operator = '='): ?object
	{
		$qb = (new QueryBuilder($this->table))->select()->where($column, $operator, $value);
		return $this->execQuery($qb, true);
	}

	public function findById(int $id): ?object
	{
		return $this->findBy('id', $id);
	}

	protected function toObject(array $data): ?object
	{
		return !empty($data) ? (object) $data : null;
	}

	protected function toObjects(array $data): array
	{
		return array_map(fn($item) => (object) $item, $data);
	}

	protected function execQuery(QueryBuilder $qb, bool $single = false): array | object | null
	{
		$queryData = $qb->getQuery();

		$stmt = $this->db->prepare($queryData['sql']);
		$stmt->execute($queryData['params']);

		$data = $single ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $single ? $this->toObject($data) : $this->toObjects($data);
	}
}
