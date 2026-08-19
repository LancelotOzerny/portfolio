<?php

namespace Controllers\Admin;

use App\Services\Blog\SymbolicCodeService;
use InvalidArgumentException;
use Models\RightsModel;
use Models\UsersModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use RuntimeException;
use Throwable;

class UserRolesController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$items = [];
		$error = trim((string) ($_GET['error'] ?? ''));

		try {
			$items = $this->loadRolesWithUsage();
		} catch (Throwable) {
			$error = $error !== '' ? $error : 'Не удалось загрузить роли.';
		}

		Template::getInstance()->setParam('title', 'Пользовательские роли');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'items' => $items,
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'deleted' => isset($_GET['deleted']) && $_GET['deleted'] === '1',
			'error' => $error,
		]);
		Template::getInstance()->showFooter();
	}

	public function create(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$data = $this->validateRole($_POST);
			$model = new RightsModel();

			if ($model->findByRole($data['role']) !== null) {
				throw new InvalidArgumentException('Роль с таким названием уже существует.');
			}

			if ($model->isCodeTaken($data['code'])) {
				throw new InvalidArgumentException('Роль с таким кодом уже существует.');
			}

			$id = $model->createRole($data['role'], $data['code'], $data['level']);
			if ($id <= 0) {
				throw new RuntimeException('Не удалось добавить роль.');
			}

			header('Location: /admin/users/roles/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/roles/', $exception);
		}
	}

	public function edit(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$item = (new RightsModel())->findById($id);
		} catch (Throwable) {
			$item = null;
		}

		if ($item === null) {
			header('Location: /admin/users/roles/?error=' . rawurlencode('Роль не найдена.'));
			return;
		}

		$usageCount = 0;
		try {
			$usageCount = (new UsersModel())->countByRightsId($id);
		} catch (Throwable) {
			$usageCount = 0;
		}

		Template::getInstance()->setParam('title', 'Редактирование роли');
		Template::getInstance()->showHeader();
		$this->render('edit', [
			'item' => $item,
			'usageCount' => $usageCount,
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'error' => trim((string) ($_GET['error'] ?? '')),
		]);
		Template::getInstance()->showFooter();
	}

	public function update(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$model = new RightsModel();

		try {
			if ($model->findById($id) === null) {
				throw new RuntimeException('Роль не найдена.');
			}

			$data = $this->validateRole($_POST);
			if ($model->findByRole($data['role'], $id) !== null) {
				throw new InvalidArgumentException('Роль с таким названием уже существует.');
			}

			if ($model->isCodeTaken($data['code'], $id)) {
				throw new InvalidArgumentException('Роль с таким кодом уже существует.');
			}

			if (!$model->updateRole($id, $data['role'], $data['code'], $data['level'])) {
				throw new RuntimeException('Не удалось сохранить изменения.');
			}

			header('Location: /admin/users/roles/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/roles/' . $id . '/', $exception);
		}
	}

	public function delete(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$model = new RightsModel();
			if ($model->findById($id) === null) {
				throw new RuntimeException('Роль не найдена.');
			}

			if ((new UsersModel())->countByRightsId($id) > 0) {
				throw new InvalidArgumentException('Нельзя удалить роль, пока она назначена пользователям.');
			}

			if (!$model->deleteById($id)) {
				throw new RuntimeException('Не удалось удалить роль.');
			}

			header('Location: /admin/users/roles/?deleted=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/roles/', $exception);
		}
	}

	private function loadRolesWithUsage(): array
	{
		$items = (new RightsModel())->findAllOrdered();
		$usersModel = new UsersModel();

		foreach ($items as $item) {
			$roleId = (int) ($item->id ?? 0);
			$item->users_count = $roleId > 0 ? $usersModel->countByRightsId($roleId) : 0;
		}

		return $items;
	}

	private function validateRole(array $input): array
	{
		$role = trim((string) ($input['role'] ?? ''));
		$levelRaw = trim((string) ($input['level'] ?? ''));
		$codeService = new SymbolicCodeService();
		$code = $codeService->normalize((string) ($input['code'] ?? ''));

		if ($role === '') {
			throw new InvalidArgumentException('Введите название роли.');
		}

		if (mb_strlen($role) > 255) {
			throw new InvalidArgumentException('Название роли не должно превышать 255 символов.');
		}

		if ($code === '') {
			$code = $codeService->fromTitle($role);
		}

		if (!$codeService->isValid($code)) {
			throw new InvalidArgumentException('Код роли должен содержать только латинские буквы, цифры, "-" и "_".');
		}

		if (mb_strlen($code) > 64) {
			throw new InvalidArgumentException('Код роли не должен превышать 64 символа.');
		}

		if ($levelRaw === '' || !preg_match('/^-?\d+$/', $levelRaw)) {
			throw new InvalidArgumentException('Укажите уровень прав целым числом.');
		}

		$level = (int) $levelRaw;
		if ($level < 0 || $level > 1000) {
			throw new InvalidArgumentException('Уровень прав должен быть от 0 до 1000.');
		}

		return [
			'role' => $role,
			'code' => strtolower($code),
			'level' => $level,
		];
	}

	private function redirectWithError(string $path, Throwable $exception): void
	{
		$message = $exception instanceof InvalidArgumentException
			? trim($exception->getMessage())
			: 'Не удалось выполнить операцию с ролью.';
		header('Location: ' . $path . '?error=' . rawurlencode($message));
	}

	private function ensureAdmin(): bool
	{
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			header('Location: /admin/login/');
			return false;
		}

		return true;
	}
}
