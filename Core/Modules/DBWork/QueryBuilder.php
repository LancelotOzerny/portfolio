<?php

namespace Modules\DBWork;

class QueryBuilder
{
	private string $sql = '';
	private array $params = [];

	private string $table;

	public function __construct($table)
	{
		$this->table = $table;
	}

	/**
	 * SELECT-запрос
	 */
	public function select(array $columns = ['*']): self
	{
		$this->sql = 'SELECT ';
		$columnList = [];

		foreach ($columns as $column) {
			$columnList[] = "$this->table.$column";
		}

		$this->sql .= implode(', ', $columnList) . " FROM $this->table";
		return $this;
	}

	/**
	 * JOIN-операция
	 */
	public function join(string $leftColumn, string $rightColumn, string $type = 'INNER'): self
	{
		$this->sql .= " $type JOIN $this->table ON $leftColumn = $rightColumn";
		return $this;
	}

	/**
	 * WHERE-условие
	 */
	public function where(string $column, string $operator, mixed $value): self
	{
		if (strpos($this->sql, 'WHERE') === false) {
			$this->sql .= ' WHERE';
		}
		else {
			$this->sql .= ' AND';
		}

		$placeholder = ':' . str_replace('.', '_', $column) . '_' . count($this->params);
		$this->sql .= " $column $operator $placeholder";
		$this->params[$placeholder] = $value;

		return $this;
	}

	/**
	 * ORDER BY
	 */
	public function orderBy(string $column, string $direction = 'ASC'): self
	{
		$this->sql .= " ORDER BY $column $direction";
		return $this;
	}

	/**
	 * LIMIT
	 */
	public function limit(int $limit): self
	{
		$this->sql .= " LIMIT $limit";
		return $this;
	}

	/**
	 * INSERT-запрос
	 */
	public function insert(array $data): self
	{
		$columns = array_keys($data);
		$placeholders = array_map(fn($col) => ':' . $col, $columns);

		$this->sql = "INSERT INTO $this->table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

		$this->params = array_combine($placeholders, array_values($data));
		return $this;
	}

	/**
	 * UPDATE-запрос
	 */
	public function update(array $data): self
	{
		$setParts = [];
		foreach ($data as $column => $value) {
			$placeholder = ':' . $column;
			$setParts[] = "$column = $placeholder";
			$this->params[$placeholder] = $value;
		}

		$this->sql = "UPDATE $this->table SET " . implode(', ', $setParts);
		return $this;
	}

	/**
	 * DELETE-запрос
	 */
	public function delete(): self
	{
		$this->sql = "DELETE FROM $this->table";
		return $this;
	}

	/**
	 * Получение готового SQL и параметров для PDO
	 */
	public function getQuery(): array
	{
		return [
			'sql' => $this->sql,
			'params' => $this->params,
		];
	}

	/**
	 * Очистка построителя
	 */
	public function reset(): self
	{
		$this->sql = '';
		$this->params = [];
		return $this;
	}
}
