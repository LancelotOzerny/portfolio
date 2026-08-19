<?php

namespace Controllers\Admin;

use App\Services\Auth\RoleCode;
use App\Services\Auth\UserRegistrationService;
use InvalidArgumentException;
use Models\RightsModel;
use Models\UsersModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Modules\Validator\AuthValidator;
use RuntimeException;
use Throwable;

class UsersController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$users = [];
		$roles = [];
		$error = trim((string) ($_GET['error'] ?? ''));

		try {
			$users = (new UsersModel())->findAllWithRights();
			$roles = (new RightsModel())->findAllOrdered();
		} catch (Throwable) {
			$error = $error !== '' ? $error : 'Не удалось загрузить список пользователей.';
			$users = [];
			$roles = [];
		}

		$currentUserId = (int) (Auth::getInstance()->getCurrentUser()?->id ?? 0);

		Template::getInstance()->setParam('title', 'Список пользователей');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'users' => $users,
			'roles' => $roles,
			'currentUserId' => $currentUserId,
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
			$validation = (new AuthValidator())->validateRegisterPayload($_POST);
			if ($validation['is_valid'] !== true) {
				$firstError = trim((string) (array_values($validation['errors'])[0] ?? ''));
				throw new InvalidArgumentException($firstError !== '' ? $firstError : 'Проверьте данные пользователя.');
			}

			$rightsId = $validation['data']['rights_id'];
			if ($rightsId === null) {
				throw new InvalidArgumentException('Выберите роль пользователя.');
			}

			(new UserRegistrationService())->register(
				$validation['data']['login'],
				$validation['data']['password'],
				$rightsId
			);

			header('Location: /admin/users/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/', $exception);
		}
	}

	public function delete(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$usersModel = new UsersModel();
			$user = $usersModel->findWithRights($id);
			if ($user === null) {
				throw new RuntimeException('Пользователь не найден.');
			}

			$currentUserId = (int) (Auth::getInstance()->getCurrentUser()?->id ?? 0);
			if ($id === $currentUserId) {
				throw new InvalidArgumentException('Нельзя удалить текущего пользователя.');
			}

			if ($this->isAdminUser($user) && $usersModel->countAdmins() <= 1) {
				throw new InvalidArgumentException('Нельзя удалить последнего администратора.');
			}

			if (!$usersModel->deleteById($id)) {
				throw new RuntimeException('Не удалось удалить пользователя.');
			}

			header('Location: /admin/users/?deleted=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/', $exception);
		}
	}

	private function isAdminUser(object $user): bool
	{
		$roleLevel = (int) ($user->role_level ?? 0);
		$roleCode = strtolower(trim((string) ($user->role_code ?? '')));
		$roleName = strtolower((string) ($user->role_name ?? ''));

		return $roleLevel >= 100 || $roleCode === RoleCode::ADMIN || $roleName === RoleCode::ADMIN;
	}

	private function redirectWithError(string $path, Throwable $exception): void
	{
		$message = $exception instanceof InvalidArgumentException
			? trim($exception->getMessage())
			: 'Не удалось выполнить операцию с пользователем.';
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
