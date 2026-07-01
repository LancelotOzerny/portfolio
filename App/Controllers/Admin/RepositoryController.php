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
		header('Location: /admin/settings/repository/');
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
