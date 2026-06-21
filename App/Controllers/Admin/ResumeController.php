<?php

namespace Controllers\Admin;

use App\Services\Resume\DescriptionSanitizer;
use DateTimeImmutable;
use Models\WorkExperienceModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class ResumeController extends BaseController
{
	public function experience(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$items = [];
		$error = trim((string) ($_GET['error'] ?? ''));

		try {
			$items = (new WorkExperienceModel())->findAllOrdered();
		} catch (Throwable) {
			$error = 'Не удалось загрузить опыт работы. Проверьте, что миграция базы данных применена.';
		}

		Template::getInstance()->setParam('title', 'Резюме — опыт работы');
		Template::getInstance()->showHeader();
		$this->render('experience', [
			'items' => $items,
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'error' => $error,
		]);
		Template::getInstance()->showFooter();
	}

	public function createExperience(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$data = $this->validateExperience($_POST);
			$id = (new WorkExperienceModel())->create(...$data);
			if ($id <= 0) {
				throw new \RuntimeException('Не удалось добавить запись.');
			}

			header('Location: /admin/resume/experience/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/resume/experience/', $exception);
		}
	}

	public function editExperience(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		try {
			$item = (new WorkExperienceModel())->findById($id);
		} catch (Throwable) {
			$item = null;
		}

		if ($item === null) {
			header('Location: /admin/resume/experience/?error=' . rawurlencode('Запись не найдена.'));
			return;
		}
		$item->description = (new DescriptionSanitizer())->sanitize((string) ($item->description ?? ''));

		Template::getInstance()->setParam('title', 'Редактирование опыта работы');
		Template::getInstance()->showHeader();
		$this->render('experience-edit', [
			'item' => $item,
			'saved' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'error' => trim((string) ($_GET['error'] ?? '')),
		]);
		Template::getInstance()->showFooter();
	}

	public function updateExperience(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$model = new WorkExperienceModel();
		try {
			if ($model->findById($id) === null) {
				throw new \RuntimeException('Запись не найдена.');
			}

			$data = $this->validateExperience($_POST);
			if (!$model->updateExperience($id, ...$data)) {
				throw new \RuntimeException('Не удалось сохранить изменения.');
			}

			header('Location: /admin/resume/experience/' . $id . '/?saved=1');
		} catch (Throwable $exception) {
			$this->redirectWithError('/admin/resume/experience/' . $id . '/', $exception);
		}
	}

	private function validateExperience(array $input): array
	{
		$position = trim((string) ($input['position'] ?? ''));
		$company = trim((string) ($input['company'] ?? ''));
		$startDate = $this->normalizeMonth((string) ($input['start_date'] ?? ''));
		$isCurrent = isset($input['is_current']);
		$endDate = $isCurrent ? null : $this->normalizeMonth((string) ($input['end_date'] ?? ''));
		$description = (new DescriptionSanitizer())->sanitize((string) ($input['description'] ?? ''));
		$active = isset($input['active']);

		if ($position === '' || $company === '') {
			throw new \InvalidArgumentException('Заполните должность и компанию.');
		}

		if (mb_strlen($position) > 255 || mb_strlen($company) > 255) {
			throw new \InvalidArgumentException('Должность и компания не должны превышать 255 символов.');
		}

		if (!$isCurrent && $endDate < $startDate) {
			throw new \InvalidArgumentException('Дата окончания не может быть раньше даты начала.');
		}

		return [$position, $company, $startDate, $endDate, $isCurrent, $description, $active];
	}

	private function normalizeMonth(string $value): string
	{
		$date = DateTimeImmutable::createFromFormat('!Y-m', trim($value));
		$errors = DateTimeImmutable::getLastErrors();
		if ($date === false || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
			throw new \InvalidArgumentException('Укажите корректные даты работы.');
		}

		return $date->format('Y-m-01');
	}

	private function redirectWithError(string $path, Throwable $exception): void
	{
		$message = $exception instanceof \InvalidArgumentException
			? trim($exception->getMessage())
			: 'Не удалось сохранить опыт работы.';
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
