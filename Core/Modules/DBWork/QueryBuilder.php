<?php

namespace Modules\DBWork;

use InvalidArgumentException;

class QueryBuilder
{
	private string $sql = '';
	private array $params = [];
	private string $table;
	private int $paramCounter = 0;

	public function __construct(string $table)
	{
		$this->table = $table;
	}

	public function table(string $table): self
	{
		$this->table = $table;
		return $this;
	}

	public function select(array $columns = ['*']): self
	{
		$this->beginQuery('SELECT ' . $this->buildSelectList($columns) . ' FROM ' . $this->table);
		return $this;
	}

	public function selectRaw(string $expression): self
	{
		$this->beginQuery('SELECT ' . trim($expression) . ' FROM ' . $this->table);
		return $this;
	}

	public function distinct(): self
	{
		if (preg_match('/^SELECT\s+/i', $this->sql) === 1 && stripos($this->sql, 'SELECT DISTINCT ') !== 0) {
			$this->sql = (string) preg_replace('/^SELECT\s+/i', 'SELECT DISTINCT ', $this->sql, 1);
		}

		return $this;
	}

	public function aggregate(string $function, string $column = '*', string $alias = 'value'): self
	{
		$function = strtoupper(trim($function));
		if (!preg_match('/^[A-Z_][A-Z0-9_]*$/', $function)) {
			throw new InvalidArgumentException('Invalid aggregate function name.');
		}

		$column = trim($column);
		$columnSql = $column === '*' ? '*' : $this->normalizeSelectColumn($column);
		$alias = trim($alias);
		$aliasSql = $alias !== '' ? ' AS ' . $alias : '';

		return $this->selectRaw($function . '(' . $columnSql . ')' . $aliasSql);
	}

	public function count(string $column = '*', string $alias = 'total'): self
	{
		return $this->aggregate('COUNT', $column, $alias);
	}

	public function avg(string $column, string $alias = 'avg_value'): self
	{
		return $this->aggregate('AVG', $column, $alias);
	}

	public function sum(string $column, string $alias = 'sum_value'): self
	{
		return $this->aggregate('SUM', $column, $alias);
	}

	public function min(string $column, string $alias = 'min_value'): self
	{
		return $this->aggregate('MIN', $column, $alias);
	}

	public function max(string $column, string $alias = 'max_value'): self
	{
		return $this->aggregate('MAX', $column, $alias);
	}

	public function join(string $table, string $leftColumn, string $rightColumn, string $type = 'INNER'): self
	{
		$type = strtoupper(trim($type));
		$allowedJoinTypes = [
			'INNER',
			'LEFT',
			'RIGHT',
			'FULL',
			'FULL OUTER',
			'LEFT OUTER',
			'RIGHT OUTER',
			'CROSS',
		];

		if (!in_array($type, $allowedJoinTypes, true)) {
			$type = 'INNER';
		}

		$this->sql .= " {$type} JOIN {$table} ON {$leftColumn} = {$rightColumn}";
		return $this;
	}

	public function where(string $column, string $operator, mixed $value): self
	{
		return $this->appendWhereCondition($column, $operator, $value, 'AND');
	}

	public function orWhere(string $column, string $operator, mixed $value): self
	{
		return $this->appendWhereCondition($column, $operator, $value, 'OR');
	}

	public function whereIn(string $column, array $values): self
	{
		return $this->appendWhereInCondition($column, $values, false, 'AND');
	}

	public function orWhereIn(string $column, array $values): self
	{
		return $this->appendWhereInCondition($column, $values, false, 'OR');
	}

	public function whereNotIn(string $column, array $values): self
	{
		return $this->appendWhereInCondition($column, $values, true, 'AND');
	}

	public function whereNull(string $column): self
	{
		$this->appendCondition('WHERE', "{$column} IS NULL", 'AND');
		return $this;
	}

	public function whereNotNull(string $column): self
	{
		$this->appendCondition('WHERE', "{$column} IS NOT NULL", 'AND');
		return $this;
	}

	public function whereRaw(string $rawCondition, array $params = [], string $connector = 'AND'): self
	{
		$this->appendCondition('WHERE', trim($rawCondition), strtoupper(trim($connector)) === 'OR' ? 'OR' : 'AND');
		$this->mergeParams($params);

		return $this;
	}

	public function groupBy(string|array $columns): self
	{
		$columnList = is_array($columns) ? $columns : [$columns];
		$parts = [];

		foreach ($columnList as $column) {
			$parts[] = $this->normalizeSelectColumn((string) $column);
		}

		$groupSql = implode(', ', $parts);
		if ($groupSql === '') {
			return $this;
		}

		if (stripos($this->sql, ' GROUP BY ') === false) {
			$this->sql .= ' GROUP BY ' . $groupSql;
			return $this;
		}

		$this->sql .= ', ' . $groupSql;
		return $this;
	}

	public function having(string $column, string $operator, mixed $value): self
	{
		$placeholder = $this->addParam($column, $value);
		$this->appendCondition('HAVING', "{$column} {$operator} {$placeholder}", 'AND');
		return $this;
	}

	public function orHaving(string $column, string $operator, mixed $value): self
	{
		$placeholder = $this->addParam($column, $value);
		$this->appendCondition('HAVING', "{$column} {$operator} {$placeholder}", 'OR');
		return $this;
	}

	public function havingRaw(string $rawCondition, array $params = [], string $connector = 'AND'): self
	{
		$this->appendCondition('HAVING', trim($rawCondition), strtoupper(trim($connector)) === 'OR' ? 'OR' : 'AND');
		$this->mergeParams($params);

		return $this;
	}

	public function orderBy(string $column, string $direction = 'ASC'): self
	{
		$direction = strtoupper(trim($direction));
		if (!in_array($direction, ['ASC', 'DESC'], true)) {
			$direction = 'ASC';
		}

		if (stripos($this->sql, ' ORDER BY ') === false) {
			$this->sql .= " ORDER BY {$column} {$direction}";
			return $this;
		}

		$this->sql .= ", {$column} {$direction}";
		return $this;
	}

	public function limit(int $limit, ?int $offset = null): self
	{
		$limit = max(0, $limit);
		$this->removeClause('/\sLIMIT\s+\d+(\s*,\s*\d+)?(\s+OFFSET\s+\d+)?/i');

		$this->sql .= ' LIMIT ' . $limit;
		if ($offset !== null) {
			$this->sql .= ' OFFSET ' . max(0, $offset);
		}

		return $this;
	}

	public function offset(int $offset): self
	{
		$offset = max(0, $offset);

		if (stripos($this->sql, ' LIMIT ') === false) {
			$this->sql .= ' LIMIT 18446744073709551615';
		}

		$this->removeClause('/\sOFFSET\s+\d+/i');
		$this->sql .= ' OFFSET ' . $offset;
		return $this;
	}

	public function page(int $page, int $perPage): self
	{
		$page = max(1, $page);
		$perPage = max(1, $perPage);
		$offset = ($page - 1) * $perPage;
		return $this->limit($perPage, $offset);
	}

	public function insert(array $data): self
	{
		if ($data === []) {
			throw new InvalidArgumentException('Insert data cannot be empty.');
		}

		$this->beginQuery('');

		$columns = array_keys($data);
		$placeholders = [];

		foreach ($columns as $column) {
			$placeholders[] = $this->addParam('ins_' . (string) $column, $data[$column]);
		}

		$this->sql = 'INSERT INTO ' . $this->table
			. ' (' . implode(', ', $columns) . ')'
			. ' VALUES (' . implode(', ', $placeholders) . ')';

		return $this;
	}

	public function update(array $data): self
	{
		if ($data === []) {
			throw new InvalidArgumentException('Update data cannot be empty.');
		}

		$this->beginQuery('');
		$setParts = [];

		foreach ($data as $column => $value) {
			$placeholder = $this->addParam('upd_' . (string) $column, $value);
			$setParts[] = "{$column} = {$placeholder}";
		}

		$this->sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $setParts);
		return $this;
	}

	public function delete(): self
	{
		$this->beginQuery('DELETE FROM ' . $this->table);
		return $this;
	}

	public function createTable(array $columns, bool $ifNotExists = true, array $options = []): self
	{
		if ($columns === []) {
			throw new InvalidArgumentException('Columns list for CREATE TABLE cannot be empty.');
		}

		$definitions = [];
		foreach ($columns as $column => $definition) {
			if (is_int($column)) {
				$definitions[] = trim((string) $definition);
				continue;
			}

			$definitions[] = $this->quoteIdentifier((string) $column) . ' ' . trim((string) $definition);
		}

		$sql = 'CREATE TABLE ';
		if ($ifNotExists) {
			$sql .= 'IF NOT EXISTS ';
		}
		$sql .= $this->table . ' (' . implode(', ', $definitions) . ')';

		$engine = isset($options['engine']) ? trim((string) $options['engine']) : '';
		$charset = isset($options['charset']) ? trim((string) $options['charset']) : '';
		$collate = isset($options['collate']) ? trim((string) $options['collate']) : '';

		if ($engine !== '') {
			$sql .= ' ENGINE=' . $engine;
		}
		if ($charset !== '') {
			$sql .= ' DEFAULT CHARSET=' . $charset;
		}
		if ($collate !== '') {
			$sql .= ' COLLATE=' . $collate;
		}

		$this->beginQuery($sql);
		return $this;
	}

	public function dropTable(bool $ifExists = true): self
	{
		$sql = 'DROP TABLE ';
		if ($ifExists) {
			$sql .= 'IF EXISTS ';
		}
		$sql .= $this->table;

		$this->beginQuery($sql);
		return $this;
	}

	public function truncateTable(): self
	{
		$this->beginQuery('TRUNCATE TABLE ' . $this->table);
		return $this;
	}

	public function renameTable(string $newTable): self
	{
		$newTable = trim($newTable);
		if ($newTable === '') {
			throw new InvalidArgumentException('New table name cannot be empty.');
		}

		$this->beginQuery('RENAME TABLE ' . $this->table . ' TO ' . $newTable);
		$this->table = $newTable;
		return $this;
	}

	public function addColumn(string $column, string $definition, ?string $after = null, bool $first = false): self
	{
		$sql = 'ALTER TABLE ' . $this->table . ' ADD COLUMN ' . $this->quoteIdentifier($column) . ' ' . trim($definition);
		if ($first) {
			$sql .= ' FIRST';
		} elseif ($after !== null && trim($after) !== '') {
			$sql .= ' AFTER ' . $this->quoteIdentifier($after);
		}

		$this->beginQuery($sql);
		return $this;
	}

	public function modifyColumn(string $column, string $definition): self
	{
		$sql = 'ALTER TABLE ' . $this->table . ' MODIFY COLUMN ' . $this->quoteIdentifier($column) . ' ' . trim($definition);
		$this->beginQuery($sql);
		return $this;
	}

	public function dropColumn(string $column): self
	{
		$sql = 'ALTER TABLE ' . $this->table . ' DROP COLUMN ' . $this->quoteIdentifier($column);
		$this->beginQuery($sql);
		return $this;
	}

	public function renameColumn(string $oldColumn, string $newColumn, ?string $definition = null): self
	{
		$oldColumnQuoted = $this->quoteIdentifier($oldColumn);
		$newColumnQuoted = $this->quoteIdentifier($newColumn);

		if ($definition !== null && trim($definition) !== '') {
			$sql = 'ALTER TABLE ' . $this->table . ' CHANGE ' . $oldColumnQuoted . ' ' . $newColumnQuoted . ' ' . trim($definition);
			$this->beginQuery($sql);
			return $this;
		}

		$sql = 'ALTER TABLE ' . $this->table . ' RENAME COLUMN ' . $oldColumnQuoted . ' TO ' . $newColumnQuoted;
		$this->beginQuery($sql);
		return $this;
	}

	public function addIndex(string $indexName, string|array $columns, bool $unique = false): self
	{
		$indexName = trim($indexName);
		if ($indexName === '') {
			throw new InvalidArgumentException('Index name cannot be empty.');
		}

		$columnList = is_array($columns) ? $columns : [$columns];
		if ($columnList === []) {
			throw new InvalidArgumentException('Index columns cannot be empty.');
		}

		$quotedColumns = [];
		foreach ($columnList as $column) {
			$quotedColumns[] = $this->quoteIdentifier((string) $column);
		}

		$sql = 'ALTER TABLE ' . $this->table
			. ' ADD '
			. ($unique ? 'UNIQUE ' : '')
			. 'INDEX '
			. $this->quoteIdentifier($indexName)
			. ' (' . implode(', ', $quotedColumns) . ')';

		$this->beginQuery($sql);
		return $this;
	}

	public function dropIndex(string $indexName): self
	{
		$indexName = trim($indexName);
		if ($indexName === '') {
			throw new InvalidArgumentException('Index name cannot be empty.');
		}

		$sql = 'ALTER TABLE ' . $this->table . ' DROP INDEX ' . $this->quoteIdentifier($indexName);
		$this->beginQuery($sql);
		return $this;
	}

	public function raw(string $sql, array $params = []): self
	{
		$this->beginQuery(trim($sql));
		$this->mergeParams($params);
		return $this;
	}

	public function getQuery(): array
	{
		return [
			'sql' => $this->sql,
			'params' => $this->params,
		];
	}

	public function reset(): self
	{
		$this->sql = '';
		$this->params = [];
		$this->paramCounter = 0;
		return $this;
	}

	private function beginQuery(string $sql): void
	{
		$this->sql = $sql;
		$this->params = [];
		$this->paramCounter = 0;
	}

	private function appendWhereCondition(string $column, string $operator, mixed $value, string $connector): self
	{
		$placeholder = $this->addParam($column, $value);
		$this->appendCondition('WHERE', "{$column} {$operator} {$placeholder}", $connector);
		return $this;
	}

	private function appendWhereInCondition(string $column, array $values, bool $isNotIn, string $connector): self
	{
		if ($values === []) {
			$this->appendCondition('WHERE', $isNotIn ? '1 = 1' : '1 = 0', $connector);
			return $this;
		}

		$placeholders = [];
		foreach (array_values($values) as $index => $value) {
			$placeholders[] = $this->addParam($column . '_in_' . $index, $value);
		}

		$operator = $isNotIn ? 'NOT IN' : 'IN';
		$this->appendCondition('WHERE', "{$column} {$operator} (" . implode(', ', $placeholders) . ')', $connector);
		return $this;
	}

	private function appendCondition(string $keyword, string $condition, string $connector): void
	{
		$keyword = strtoupper(trim($keyword));
		$connector = strtoupper(trim($connector)) === 'OR' ? 'OR' : 'AND';

		if (stripos($this->sql, ' ' . $keyword . ' ') === false) {
			$this->sql .= ' ' . $keyword . ' ' . $condition;
			return;
		}

		$this->sql .= ' ' . $connector . ' ' . $condition;
	}

	private function removeClause(string $pattern): void
	{
		$updatedSql = preg_replace($pattern, '', $this->sql);
		if (is_string($updatedSql)) {
			$this->sql = $updatedSql;
		}
	}

	private function buildSelectList(array $columns): string
	{
		$columnList = [];
		foreach ($columns as $column) {
			$columnList[] = $this->normalizeSelectColumn((string) $column);
		}

		return implode(', ', $columnList);
	}

	private function normalizeSelectColumn(string $column): string
	{
		$column = trim($column);
		if ($column === '*') {
			return $this->table . '.*';
		}

		if (!$this->shouldPrefixColumn($column)) {
			return $column;
		}

		return $this->table . '.' . $column;
	}

	private function shouldPrefixColumn(string $column): bool
	{
		$markers = ['.', '(', ')', ' ', '`', ',', '*'];
		foreach ($markers as $marker) {
			if (str_contains($column, $marker)) {
				return false;
			}
		}

		return true;
	}

	private function addParam(string $key, mixed $value): string
	{
		$key = preg_replace('/[^a-zA-Z0-9_]/', '_', $key) ?? 'param';
		$placeholder = ':' . $key . '_' . $this->paramCounter++;
		$this->params[$placeholder] = $value;
		return $placeholder;
	}

	private function mergeParams(array $params): void
	{
		foreach ($params as $key => $value) {
			$placeholder = (string) $key;
			if ($placeholder === '') {
				continue;
			}

			if (!str_starts_with($placeholder, ':')) {
				$placeholder = ':' . $placeholder;
			}

			$this->params[$placeholder] = $value;
		}
	}

	private function quoteIdentifier(string $identifier): string
	{
		$identifier = trim($identifier);
		if ($identifier === '' || str_contains($identifier, ' ')) {
			return $identifier;
		}

		$parts = explode('.', $identifier);
		$quoted = array_map(
			static function (string $part): string {
				$part = trim($part);
				if ($part === '*' || $part === '') {
					return $part;
				}

				return '`' . str_replace('`', '', $part) . '`';
			},
			$parts
		);

		return implode('.', $quoted);
	}
}
