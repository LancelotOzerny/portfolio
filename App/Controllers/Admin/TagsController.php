<?php

namespace Controllers\Admin;

use App\Services\Security\CsrfService;
use Models\ProjectsModel;
use Models\TagsModel;
use Modules\DBWork\DBConnection;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class TagsController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$items = [];
		$error = trim((string) ($_GET['error'] ?? ''));

		try {
			$items = $this->loadTagsWithUsage();
		} catch (Throwable) {
			$error = 'Не удалось загрузить категории.';
		}

		Template::getInstance()->setParam('title', 'Контент — категории проектов');
		Template::getInstance()->showHeader();
		$this->render('index', [
			'items' => $items,
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'deleted' => isset($_GET['deleted']) && $_GET['deleted'] === '1',
			'deletedCount' => (int) ($_GET['count'] ?? 0),
			'error' => $error,
			'csrfToken' => (new CsrfService())->getToken(),
		]);
		Template::getInstance()->showFooter();
	}

	public function create(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$data = $this->validateTag($_POST);
			$model = new TagsModel();

			if ($model->findByName($data['name']) !== null) {
				throw new \InvalidArgumentException('Категория с таким названием уже существует.');
			}

			$id = $model->create($data['name'], false);
			if ($id <= 0) {
				throw new \RuntimeException('Не удалось добавить категорию.');
			}

			header('Location: /admin/content/tags/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/content/tags/', $exception);
		}
	}

	public function edit(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$item = (new TagsModel())->findById($id);
		} catch (Throwable) {
			$item = null;
		}

		if ($item === null) {
			header('Location: /admin/content/tags/?error=' . rawurlencode('Категория не найдена.'));
			return;
		}

		$usageCount = 0;
		try {
			$usageCount = (new ProjectsModel())->countByCategoryId($id);
		} catch (Throwable) {
			$usageCount = 0;
		}

		Template::getInstance()->setParam('title', 'Редактирование категории');
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

		$model = new TagsModel();

		try {
			if ($model->findById($id) === null) {
				throw new \RuntimeException('Категория не найдена.');
			}

			$data = $this->validateTag($_POST);
			if ($model->findByName($data['name'], $id) !== null) {
				throw new \InvalidArgumentException('Категория с таким названием уже существует.');
			}

			if (!$model->updateTag($id, $data['name'], false)) {
				throw new \RuntimeException('Не удалось сохранить категорию.');
			}

			header('Location: /admin/content/tags/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/content/tags/' . $id . '/', $exception);
		}
	}

	public function delete(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$tagsModel = new TagsModel();
		$projectsModel = new ProjectsModel();
		$connection = DBConnection::getConnection();
		$transactionStarted = false;

		try {
			if ($tagsModel->findById($id) === null) {
				throw new \RuntimeException('Категория не найдена.');
			}

			if (!$connection->inTransaction()) {
				$connection->beginTransaction();
				$transactionStarted = true;
			}

			if (!$projectsModel->clearCategoryId($id)) {
				throw new \RuntimeException('Не удалось удалить категорию у проектов.');
			}

			if (!$tagsModel->deleteTag($id)) {
				throw new \RuntimeException('Не удалось удалить категорию.');
			}

			if ($transactionStarted && $connection->inTransaction()) {
				$connection->commit();
			}

			header('Location: /admin/content/tags/?deleted=1');
		} catch (Throwable $exception) {
			if ($transactionStarted && $connection->inTransaction()) {
				$connection->rollBack();
			}

			$this->redirectWithError('/admin/content/tags/', $exception);
		}
	}

	public function bulkDelete(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			header('Location: /admin/content/tags/?error=' . rawurlencode('Недействительный CSRF-токен.'));
			return;
		}

		$categoryIds = $this->normalizeCategoryIds($_POST['category_ids'] ?? []);
		if ($categoryIds === []) {
			header('Location: /admin/content/tags/?error=' . rawurlencode('Выберите хотя бы одну категорию.'));
			return;
		}

		$categoriesModel = new TagsModel();
		$projectsModel = new ProjectsModel();
		$connection = DBConnection::getConnection();
		$transactionStarted = false;
		$deletedCount = 0;

		try {
			if (!$connection->inTransaction()) {
				$connection->beginTransaction();
				$transactionStarted = true;
			}

			foreach ($categoryIds as $categoryId) {
				if ($categoriesModel->findById($categoryId) === null) {
					continue;
				}

				if (!$projectsModel->clearCategoryId($categoryId)) {
					throw new \RuntimeException('Не удалось снять категорию у проектов.');
				}

				if (!$categoriesModel->deleteTag($categoryId)) {
					throw new \RuntimeException('Не удалось удалить категорию.');
				}

				$deletedCount++;
			}

			if ($transactionStarted && $connection->inTransaction()) {
				$connection->commit();
			}

			header('Location: /admin/content/tags/?deleted=1&count=' . $deletedCount);
		} catch (Throwable $exception) {
			if ($transactionStarted && $connection->inTransaction()) {
				$connection->rollBack();
			}

			$this->redirectWithError('/admin/content/tags/', $exception);
		}
	}

	private function loadTagsWithUsage(): array
	{
		$items = (new TagsModel())->findAllOrdered();
		$projectsModel = new ProjectsModel();

		foreach ($items as $item) {
			$tagId = (int) ($item->id ?? 0);
			$item->projects_count = $tagId > 0 ? $projectsModel->countByCategoryId($tagId) : 0;
		}

		return $items;
	}

	private function normalizeCategoryIds(mixed $rawCategoryIds): array
	{
		if (!is_array($rawCategoryIds)) {
			return [];
		}

		$categoryIds = [];
		foreach ($rawCategoryIds as $categoryId) {
			$categoryId = (int) $categoryId;
			if ($categoryId > 0) {
				$categoryIds[$categoryId] = $categoryId;
			}
		}

		return array_values($categoryIds);
	}

	private function validateTag(array $input): array
	{
		$name = trim((string) ($input['name'] ?? ''));
		if ($name === '') {
			throw new \InvalidArgumentException('Введите название категории.');
		}

		if (mb_strlen($name) > 255) {
			throw new \InvalidArgumentException('Название категории не должно превышать 255 символов.');
		}

		return [
			'name' => $name,
			'use_as_filter' => false,
		];
	}

	private function redirectWithError(string $path, Throwable $exception): void
	{
		$message = $exception instanceof \InvalidArgumentException
			? trim($exception->getMessage())
			: 'Не удалось выполнить операцию с категорией.';
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
