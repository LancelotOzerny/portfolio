<?php

namespace Controllers\Admin;

use Modules\Main\App;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;

class RepositoryController extends BaseController
{
	private const FLASH_KEY = 'admin_repository_update';
	private const TARGET_BRANCH = 'main';

	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Репозиторий');

		$result = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		Template::getInstance()->showHeader();
		$this->render('index', [
			'updateResult' => $result,
		]);
		Template::getInstance()->showFooter();
	}

	public function update(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$repoRoot = App::getInstance()->root;
		$result = [
			'success' => false,
			'message' => '',
			'output' => '',
		];

		if (!function_exists('exec')) {
			$result['message'] = 'Команда exec недоступна в PHP.';
			$this->storeFlashAndRedirect($result);
			return;
		}

		if (!is_dir($repoRoot . DIRECTORY_SEPARATOR . '.git')) {
			$result['message'] = 'Папка .git не найдена. Невозможно обновить репозиторий.';
			$this->storeFlashAndRedirect($result);
			return;
		}

		$branchResult = $this->runGitCommand($repoRoot, 'rev-parse --abbrev-ref HEAD');
		if (!$branchResult['success']) {
			$result['message'] = 'Не удалось определить текущую ветку.';
			$result['output'] = $branchResult['output'];
			$this->storeFlashAndRedirect($result);
			return;
		}

		$currentBranch = trim($branchResult['output']);
		if ($currentBranch !== self::TARGET_BRANCH) {
			$result['message'] = "Текущая ветка: {$currentBranch}. Для обновления ожидается ветка " . self::TARGET_BRANCH . '.';
			$result['output'] = $branchResult['output'];
			$this->storeFlashAndRedirect($result);
			return;
		}

		$pullResult = $this->runGitCommand($repoRoot, 'pull origin ' . self::TARGET_BRANCH);
		$result['success'] = $pullResult['success'];
		$result['message'] = $pullResult['success']
			? 'Репозиторий успешно обновлен (main).'
			: 'Не удалось обновить репозиторий.';
		$result['output'] = $pullResult['output'];

		$this->storeFlashAndRedirect($result);
	}

	public function save(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$repoRoot = App::getInstance()->root;
		$result = [
			'success' => false,
			'message' => '',
			'output' => '',
		];

		if (!function_exists('exec')) {
			$result['message'] = 'Команда exec недоступна в PHP.';
			$this->storeFlashAndRedirect($result);
			return;
		}

		if (!is_dir($repoRoot . DIRECTORY_SEPARATOR . '.git')) {
			$result['message'] = 'Папка .git не найдена. Невозможно сохранить изменения.';
			$this->storeFlashAndRedirect($result);
			return;
		}

		$statusResult = $this->runGitCommand($repoRoot, 'status --porcelain');
		if (!$statusResult['success']) {
			$result['message'] = 'Не удалось проверить изменения в репозитории.';
			$result['output'] = $statusResult['output'];
			$this->storeFlashAndRedirect($result);
			return;
		}

		$commitMessage = trim((string) ($_POST['commit_message'] ?? ''));
		$result['commitMessage'] = $commitMessage;

		if ($commitMessage === '') {
			$result['message'] = 'Укажите текст коммита.';
			$this->storeFlashAndRedirect($result);
			return;
		}

		if (trim($statusResult['output']) === '') {
			$result['success'] = true;
			$result['message'] = 'Нет изменений для сохранения.';
			$this->storeFlashAndRedirect($result);
			return;
		}

		$addResult = $this->runGitCommand($repoRoot, 'add -A');
		if (!$addResult['success']) {
			$result['message'] = 'Не удалось подготовить изменения к сохранению.';
			$result['output'] = $addResult['output'];
			$this->storeFlashAndRedirect($result);
			return;
		}

		$commitResult = $this->runGitCommit($repoRoot, $commitMessage);
		if (!$commitResult['success']) {
			$result['message'] = 'Не удалось создать коммит с изменениями.';
			$result['output'] = trim($addResult['output'] . PHP_EOL . $commitResult['output']);
			$this->storeFlashAndRedirect($result);
			return;
		}

		$pushResult = $this->runGitCommand($repoRoot, 'push');
		$result['success'] = $pushResult['success'];
		$result['message'] = $pushResult['success']
			? 'Изменения сохранены и отправлены в репозиторий.'
			: 'Коммит создан, но отправить изменения не удалось.';
		$result['output'] = trim($addResult['output'] . PHP_EOL . $commitResult['output'] . PHP_EOL . $pushResult['output']);

		$this->storeFlashAndRedirect($result);
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

	private function storeFlashAndRedirect(array $result): void
	{
		$_SESSION[self::FLASH_KEY] = $result;
		header('Location: /admin/development/repository/');
	}

	private function runGitCommit(string $repoRoot, string $message): array
	{
		[$name, $email] = $this->resolveCommitIdentity();

		$command = sprintf(
			'-c user.name=%s -c user.email=%s commit -m %s',
			escapeshellarg($name),
			escapeshellarg($email),
			escapeshellarg($message)
		);

		return $this->runGitCommand($repoRoot, $command);
	}

	/**
	 * @return array{0: string, 1: string}
	 */
	private function resolveCommitIdentity(): array
	{
		$user = Auth::getInstance()->getCurrentUser();
		$name = trim((string) ($user->login ?? ''));
		$email = trim((string) ($user->email ?? ''));

		if ($name === '') {
			$name = 'LANCY Admin';
		}

		if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$email = 'admin@lancy.studio';
		}

		return [$name, $email];
	}

	private function runGitCommand(string $repoRoot, string $command): array
	{
		$fullCommand = 'git -C ' . escapeshellarg($repoRoot) . ' ' . $command . ' 2>&1';
		$output = [];
		$exitCode = 1;
		exec($fullCommand, $output, $exitCode);

		return [
			'success' => $exitCode === 0,
			'output' => trim(implode(PHP_EOL, $output)),
		];
	}
}
