<?php

namespace Controllers\Admin;

use App\Services\ApiToken\ApiTokenService;
use App\Services\Auth\PasswordVerifier;
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
	private const TOKEN_FLASH_KEY = 'admin_user_plain_token';

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

	public function detail(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$user = $this->loadUser($id);
		if ($user === null) {
			header('Location: /admin/users/?error=' . rawurlencode('Пользователь не найден.'));
			return;
		}

		$token = null;
		try {
			$token = (new ApiTokenService())->findActive($id);
		} catch (Throwable) {
			$token = null;
		}

		$plainToken = $this->pullPlainToken();
		$currentUserId = (int) (Auth::getInstance()->getCurrentUser()?->id ?? 0);

		Template::getInstance()->setParam('title', 'Пользователь ' . (string) ($user->login ?? ''));
		Template::getInstance()->showHeader();
		$this->render('detail', [
			'user' => $user,
			'token' => $token,
			'plainToken' => $plainToken,
			'currentUserId' => $currentUserId,
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'error' => trim((string) ($_GET['error'] ?? '')),
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

	public function generateToken(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			if ($this->loadUser($id) === null) {
				throw new RuntimeException('Пользователь не найден.');
			}

			$service = new ApiTokenService();
			if ($service->hasActive($id)) {
				throw new InvalidArgumentException('У пользователя уже есть действующий токен. Получите его или перегенерируйте.');
			}

			$issued = $service->issue($id);
			$this->storePlainToken($issued->token);
			header('Location: /admin/users/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/' . $id . '/', $exception);
		}
	}

	public function revealToken(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			if ($this->loadUser($id) === null) {
				throw new RuntimeException('Пользователь не найден.');
			}

			$this->assertCurrentAdminPassword((string) ($_POST['password'] ?? ''));
			$token = (new ApiTokenService())->reveal($id);
			$this->storePlainToken($token);
			header('Location: /admin/users/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/' . $id . '/', $exception);
		}
	}

	public function regenerateToken(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			if ($this->loadUser($id) === null) {
				throw new RuntimeException('Пользователь не найден.');
			}

			$issued = (new ApiTokenService())->regenerate($id);
			$this->storePlainToken($issued->token);
			header('Location: /admin/users/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/' . $id . '/', $exception);
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

			(new ApiTokenService())->revoke($id);

			if (!$usersModel->deleteById($id)) {
				throw new RuntimeException('Не удалось удалить пользователя.');
			}

			header('Location: /admin/users/?deleted=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/users/', $exception);
		}
	}

	private function loadUser(int $id): ?object
	{
		if ($id <= 0) {
			return null;
		}

		try {
			return (new UsersModel())->findWithRights($id);
		} catch (Throwable) {
			return null;
		}
	}

	private function assertCurrentAdminPassword(string $password): void
	{
		$admin = Auth::getInstance()->getCurrentUser();
		if ($admin === null || !(new PasswordVerifier())->verify($password, $admin)) {
			throw new InvalidArgumentException('Неверный пароль.');
		}
	}

	private function storePlainToken(string $token): void
	{
		$_SESSION[self::TOKEN_FLASH_KEY] = $token;
	}

	private function pullPlainToken(): string
	{
		$token = $_SESSION[self::TOKEN_FLASH_KEY] ?? '';
		unset($_SESSION[self::TOKEN_FLASH_KEY]);

		return is_string($token) ? $token : '';
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
