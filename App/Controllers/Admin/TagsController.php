<?php

namespace Controllers\Admin;

use Models\ProjectTagsModel;
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
			$error = 'Не удалось загрузить теги.';
		}

		Template::getInstance()->setParam('title', 'Контент — теги');
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
			$data = $this->validateTag($_POST);
			$model = new TagsModel();

			if ($model->findByName($data['name']) !== null) {
				throw new \InvalidArgumentException('Тег с таким названием уже существует.');
			}

			$id = $model->create($data['name'], $data['use_as_filter']);
			if ($id <= 0) {
				throw new \RuntimeException('Не удалось добавить тег.');
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
			header('Location: /admin/content/tags/?error=' . rawurlencode('Тег не найден.'));
			return;
		}

		$usageCount = 0;
		try {
			$usageCount = (new ProjectTagsModel())->countByTagId($id);
		} catch (Throwable) {
			$usageCount = 0;
		}

		Template::getInstance()->setParam('title', 'Редактирование тега');
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
				throw new \RuntimeException('Тег не найден.');
			}

			$data = $this->validateTag($_POST);
			if ($model->findByName($data['name'], $id) !== null) {
				throw new \InvalidArgumentException('Тег с таким названием уже существует.');
			}

			if (!$model->updateTag($id, $data['name'], $data['use_as_filter'])) {
				throw new \RuntimeException('Не удалось сохранить изменения.');
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
		$relationsModel = new ProjectTagsModel();
		$connection = DBConnection::getConnection();
		$transactionStarted = false;

		try {
			if ($tagsModel->findById($id) === null) {
				throw new \RuntimeException('Тег не найден.');
			}

			if (!$connection->inTransaction()) {
				$connection->beginTransaction();
				$transactionStarted = true;
			}

			if (!$relationsModel->deleteByTagId($id)) {
				throw new \RuntimeException('Не удалось удалить связи тега с проектами.');
			}

			if (!$tagsModel->deleteTag($id)) {
				throw new \RuntimeException('Не удалось удалить тег.');
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

	private function loadTagsWithUsage(): array
	{
		$items = (new TagsModel())->findAllOrdered();
		$relationsModel = new ProjectTagsModel();

		foreach ($items as $item) {
			$tagId = (int) ($item->id ?? 0);
			$item->projects_count = $tagId > 0 ? $relationsModel->countByTagId($tagId) : 0;
		}

		return $items;
	}

	private function validateTag(array $input): array
	{
		$name = trim((string) ($input['name'] ?? ''));
		$useAsFilter = isset($input['use_as_filter']);

		if ($name === '') {
			throw new \InvalidArgumentException('Введите название тега.');
		}

		if (mb_strlen($name) > 255) {
			throw new \InvalidArgumentException('Название тега не должно превышать 255 символов.');
		}

		return [
			'name' => $name,
			'use_as_filter' => $useAsFilter,
		];
	}

	private function redirectWithError(string $path, Throwable $exception): void
	{
		$message = $exception instanceof \InvalidArgumentException
			? trim($exception->getMessage())
			: 'Не удалось выполнить операцию с тегом.';
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
