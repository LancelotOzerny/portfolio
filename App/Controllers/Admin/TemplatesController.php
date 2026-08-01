<?php

namespace Controllers\Admin;

use App\Services\Templates\TemplateCatalogService;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class TemplatesController extends BaseController
{
	private const FLASH_KEY = 'admin_templates_flash';

	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Шаблоны');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'templates' => (new TemplateCatalogService())->list(),
			'flash' => $this->pullFlash(),
		]);
		Template::getInstance()->showFooter();
	}

	public function create(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'Создание шаблона');
		Template::getInstance()->showHeader();
		$this->render('create', [
			'flash' => $this->pullFlash(),
		]);
		Template::getInstance()->showFooter();
	}

	public function store(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			(new TemplateCatalogService())->create(
				(string) ($_POST['code'] ?? ''),
				(string) ($_POST['name'] ?? ''),
				(string) ($_POST['description'] ?? '')
			);
			$this->setFlash(true, 'Шаблон успешно создан.');
			header('Location: /admin/settings/templates/');
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage() !== '' ? $e->getMessage() : 'Не удалось создать шаблон.');
			header('Location: /admin/settings/templates/create/');
		}
	}

	public function delete(string $code): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			(new TemplateCatalogService())->delete($code);
			$this->setFlash(true, 'Шаблон удален.');
		} catch (Throwable $e) {
			$this->setFlash(false, $e->getMessage() !== '' ? $e->getMessage() : 'Не удалось удалить шаблон.');
		}

		header('Location: /admin/settings/templates/');
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

	private function setFlash(bool $success, string $message): void
	{
		$_SESSION[self::FLASH_KEY] = [
			'success' => $success,
			'message' => $message,
		];
	}

	private function pullFlash(): ?array
	{
		$flash = $_SESSION[self::FLASH_KEY] ?? null;
		unset($_SESSION[self::FLASH_KEY]);

		return is_array($flash) ? $flash : null;
	}
}
