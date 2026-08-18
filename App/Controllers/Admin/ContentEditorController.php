<?php

namespace Controllers\Admin;

use App\Services\ContentEditor\ContentEditorUploadService;
use App\Services\Security\CsrfService;
use Models\BlogArticlesModel;
use Models\ProjectsModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Throwable;

class ContentEditorController extends BaseController
{
	/**
	 * @var array<string, array{dir: string, prefix: string}>
	 */
	private const SCOPES = [
		'article' => [
			'dir' => 'articles',
			'prefix' => 'article',
		],
		'project' => [
			'dir' => 'projects',
			'prefix' => 'project',
		],
	];

	public function uploadImage(): void
	{
		$this->handleUpload(true);
	}

	public function uploadFile(): void
	{
		$this->handleUpload(false);
	}

	private function handleUpload(bool $isImage): void
	{
		header('Content-Type: application/json; charset=utf-8');

		if (!$this->ensureAdminJson()) {
			return;
		}

		if (!(new CsrfService())->validate((string) ($_POST['_csrf'] ?? ''))) {
			http_response_code(400);
			echo json_encode(['success' => 0, 'error' => 'Invalid CSRF token.']);
			return;
		}

		$scope = (string) ($_POST['scope'] ?? '');
		$entityId = (int) ($_POST['entity_id'] ?? 0);
		$scopeConfig = self::SCOPES[$scope] ?? null;
		if ($scopeConfig === null) {
			http_response_code(400);
			echo json_encode(['success' => 0, 'error' => 'Invalid upload scope.']);
			return;
		}

		if (!$this->ensureEntity($scope, $entityId)) {
			return;
		}

		try {
			$service = new ContentEditorUploadService();
			if ($isImage) {
				$url = $service->saveImage($entityId, 'image', $scopeConfig['dir'], $scopeConfig['prefix']);
				echo json_encode([
					'success' => 1,
					'file' => [
						'url' => $url,
					],
				]);
				return;
			}

			$file = $service->saveFile($entityId, 'file', $scopeConfig['dir'], $scopeConfig['prefix']);
			echo json_encode([
				'success' => 1,
				'file' => $file,
			]);
		} catch (Throwable $e) {
			http_response_code(400);
			$message = trim($e->getMessage());
			echo json_encode([
				'success' => 0,
				'error' => $message !== '' ? $message : 'Unable to upload file.',
			]);
		}
	}

	private function ensureEntity(string $scope, int $entityId): bool
	{
		try {
			if ($scope === 'article') {
				if ($entityId === 0) {
					return true;
				}

				if ((new BlogArticlesModel())->findById($entityId) === null) {
					http_response_code(400);
					echo json_encode(['success' => 0, 'error' => 'Invalid article.']);
					return false;
				}

				return true;
			}

			if ($entityId <= 0 || (new ProjectsModel())->findBaseById($entityId) === null) {
				http_response_code(400);
				echo json_encode(['success' => 0, 'error' => 'Invalid project.']);
				return false;
			}

			return true;
		} catch (Throwable) {
			http_response_code(400);
			echo json_encode(['success' => 0, 'error' => 'Unable to validate upload target.']);
			return false;
		}
	}

	private function ensureAdminJson(): bool
	{
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			http_response_code(403);
			echo json_encode(['success' => 0, 'error' => 'Access denied.']);
			return false;
		}

		return true;
	}
}
