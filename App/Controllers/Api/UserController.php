<?php
namespace Controllers\Api;

use Modules\Main\Config;
use Modules\DBWork\DBConnection;
use Modules\DBWork\QueryBuilder;

class UserController
{
	public function __construct()
	{
		header('Content-Type: application/json; charset=utf-8');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
		header('Access-Control-Allow-Headers: Content-Type');

		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			http_response_code(200);
			exit();
		}
	}

	// GET /api/users
	public function index()
	{
		$qb = new QueryBuilder('users');
		$qb->select(['id', 'name', 'email', 'created_at']);
		$qb->orderBy('id', 'DESC');

		$query = $qb->getQuery();
		$stmt = DBConnection::getConnection()->prepare($query['sql']);
		$stmt->execute($query['params']);
		$users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

		http_response_code(200);
		echo json_encode($users);
	}

	// GET /api/users/{id}
	public function show($id)
	{
		$qb = new QueryBuilder('users');
		$qb->select(['id', 'name', 'email', 'created_at']);
		$qb->where('id', '=', $id);

		$query = $qb->getQuery();
		$stmt = DBConnection::getConnection()->prepare($query['sql']);
		$stmt->execute($query['params']);
		$user = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$user) {
			http_response_code(404);
			echo json_encode(['error' => 'User not found']);
			return;
		}

		http_response_code(200);
		echo json_encode($user);
	}

	// POST /api/users
	public function store()
	{
		$input = $this->getInput();

		$qb = new QueryBuilder('users');
		$qb->insert([
			'name' => $input['name'] ?? '',
			'email' => $input['email'] ?? ''
		]);

		$query = $qb->getQuery();
		$stmt = DBConnection::getConnection()->prepare($query['sql']);

		try {
			$stmt->execute($query['params']);
			$id = DBConnection::getConnection()->lastInsertId();

			http_response_code(201);
			echo json_encode([
				'id' => $id,
				'message' => 'User created',
				'user' => $this->show($id)
			]);
		} catch (\PDOException $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
		}
	}

	// PUT /api/users/{id} — полное/частичное обновление
	public function update($id)
	{
		$input = $this->getInput();

		// Только разрешенные поля
		$updateData = array_filter([
			'name' => $input['name'] ?? null,
			'email' => $input['email'] ?? null,
		], fn($v) => $v !== null);

		if (empty($updateData)) {
			http_response_code(422);
			echo json_encode(['error' => 'No data to update']);
			return;
		}

		$qb = new QueryBuilder('users');
		$qb->update($updateData);
		$qb->where('id', '=', $id);

		$query = $qb->getQuery();
		$stmt = DBConnection::getConnection()->prepare($query['sql']);

		try {
			$stmt->execute($query['params']);

			if ($stmt->rowCount() === 0) {
				http_response_code(404);
				echo json_encode(['error' => 'User not found']);
				return;
			}

			http_response_code(200);
			echo json_encode(['message' => 'User updated']);
		} catch (\PDOException $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Database error']);
		}
	}

	// DELETE /api/users/{id}
	public function destroy($id)
	{
		$qb = new QueryBuilder('users');
		$qb->where('id', '=', $id);
		$qb->delete();

		$query = $qb->getQuery();
		$stmt = DBConnection::getConnection()->prepare($query['sql']);

		try {
			$stmt->execute($query['params']);

			if ($stmt->rowCount() === 0) {
				http_response_code(404);
				echo json_encode(['error' => 'User not found']);
			} else {
				http_response_code(204); // No Content
			}
		} catch (\PDOException $e) {
			http_response_code(500);
			echo json_encode(['error' => 'Database error']);
		}
	}

	protected function getInput(): array
	{
		$raw = file_get_contents('php://input');

		// Windows curl fix: экранированные кавычки
		$raw = stripslashes($raw);

		$input = json_decode($raw, true);
		if (json_last_error() === JSON_ERROR_NONE) {
			return $input;
		}

		// Fallback
		parse_str($raw, $parsed);
		return $parsed ?: $_POST ?: [];
	}
}