<?php

namespace Controllers\Admin;

use Models\LinksModel;
use Models\ProjectTagsModel;
use Models\ProjectsInfoModel;
use Models\ProjectsLinkModel;
use Models\ProjectsModel;
use Models\ProjectsTagsRelationModel;
use Models\TagsModel;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Throwable;

class ProjectsController extends BaseController
{
	public function index(): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		Template::getInstance()->setParam('title', 'РџСЂРѕРµРєС‚С‹');

		$projects = [];
		try {
			$projects = (new ProjectsModel())->findAllBase();
		} catch (Throwable) {
			$projects = [];
		}

		foreach ($projects as &$project) {
			$projectId = (int) ($project->id ?? 0);
			$project->tags = $projectId > 0 ? $this->loadProjectTags($projectId) : [];
		}
		unset($project);

		Template::getInstance()->showHeader();
		$this->render('index', ['projects' => $projects]);
		Template::getInstance()->showFooter();
	}

	public function detail(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$projectsModel = new ProjectsModel();

		try {
			$project = $projectsModel->findBaseById($id);
		} catch (Throwable) {
			$project = null;
		}

		if ($project === null) {
			header('Location: /admin/projects/');
			return;
		}

		$tags = [];
		$links = [];
		$projectInfo = [];
		$allTags = [];

		$tags = $this->loadProjectTags($id);

		try {
			$links = (new ProjectsLinkModel())->findAllByProjectId($id);
		} catch (Throwable) {
			try {
				$links = (new LinksModel())->findAllByProject($id);
			} catch (Throwable) {
				$links = [];
			}
		}

		try {
			$projectInfo = (new ProjectsInfoModel())->findAllByProjectId($id);
		} catch (Throwable) {
			$projectInfo = [];
		}

		try {
			$allTags = (new TagsModel())->findAll();
		} catch (Throwable) {
			$allTags = [];
		}

		Template::getInstance()->setParam('title', 'Р РµРґР°РєС‚РёСЂРѕРІР°РЅРёРµ РїСЂРѕРµРєС‚Р° #' . $id);

		Template::getInstance()->showHeader();
		$this->render('detail', [
			'project' => $project,
			'tags' => $tags,
			'allTags' => $allTags,
			'links' => $links,
			'projectInfo' => $projectInfo,
			'saveSuccess' => isset($_GET['saved']) && $_GET['saved'] === '1',
			'saveError' => isset($_GET['error']) ? (string) $_GET['error'] : '',
		]);
		Template::getInstance()->showFooter();
	}

	public function updateMainInfo(int $id): void
	{
		if (!$this->ensureAdmin()) {
			return;
		}

		$projectsModel = new ProjectsModel();

		try {
			$project = $projectsModel->findBaseById($id);
		} catch (Throwable) {
			$project = null;
		}

		if ($project === null) {
			header('Location: /admin/projects/');
			return;
		}

		$name = trim((string) ($_POST['name'] ?? ''));
		$active = isset($_POST['active']) ? 1 : 0;
		$previewText = trim((string) ($_POST['preview_text'] ?? ''));
		$detailText = (string) ($_POST['detail_text'] ?? '');
		$previewImageUrl = trim((string) ($_POST['preview_image_url_existing'] ?? (string) ($project->preview_image_url ?? '')));
		$detailImageUrl = trim((string) ($_POST['detail_image_url_existing'] ?? (string) ($project->detail_image_url ?? '')));

		if ($name === '') {
			header('Location: /admin/projects/' . $id . '/?error=' . rawurlencode('Enter project name.'));
			return;
		}

		try {
			$previewImageUrl = $this->saveProjectImageUpload($id, 'preview_image_file', $previewImageUrl);
			$detailImageUrl = $this->saveProjectImageUpload($id, 'detail_image_file', $detailImageUrl);

			if (!$projectsModel->updateEditorData(
				$id,
				$name,
				$active,
				$previewText,
				$detailText,
				$previewImageUrl,
				$detailImageUrl
			)) {
				throw new \RuntimeException('Unable to save changes.');
			}

			header('Location: /admin/projects/' . $id . '/?saved=1');
			return;
		} catch (\RuntimeException $e) {
			$message = trim($e->getMessage());
			if ($message === '') {
				$message = 'Unable to save changes.';
			}

			header('Location: /admin/projects/' . $id . '/?error=' . rawurlencode($message));
			return;
		} catch (Throwable) {
			header('Location: /admin/projects/' . $id . '/?error=' . rawurlencode('Unable to save changes.'));
			return;
		}
	}

	private function saveProjectImageUpload(int $projectId, string $fileKey, string $existingUrl): string
	{
		$file = $_FILES[$fileKey] ?? null;
		if (!is_array($file)) {
			return $existingUrl;
		}

		$errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
		if ($errorCode === UPLOAD_ERR_NO_FILE) {
			return $existingUrl;
		}

		if ($errorCode !== UPLOAD_ERR_OK) {
			throw new \RuntimeException('Upload error for ' . $fileKey . '.');
		}

		$tmpPath = (string) ($file['tmp_name'] ?? '');
		if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
			throw new \RuntimeException('Invalid uploaded file for ' . $fileKey . '.');
		}

		$mime = $this->detectImageMimeType($tmpPath);
		$allowedMimeToExt = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/webp' => 'webp',
		];

		if (!isset($allowedMimeToExt[$mime])) {
			throw new \RuntimeException('Only JPG/PNG/GIF/WEBP are allowed for ' . $fileKey . '.');
		}

		$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
		if ($documentRoot === '') {
			throw new \RuntimeException('Document root is not configured.');
		}

		$uploadDir = $documentRoot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'projects';
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
			throw new \RuntimeException('Unable to create upload directory.');
		}

		$imageType = $fileKey === 'preview_image_file' ? 'preview' : 'detail';
		$fileName = sprintf(
			'project_%d_%s_%s_%s.%s',
			$projectId,
			$imageType,
			date('Ymd_His'),
			bin2hex(random_bytes(4)),
			$allowedMimeToExt[$mime]
		);
		$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
			throw new \RuntimeException('Unable to move uploaded file for ' . $fileKey . '.');
		}

		return '/upload/images/projects/' . $fileName;
	}

	private function detectImageMimeType(string $filePath): string
	{
		$mime = '';

		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			if ($finfo !== false) {
				$detected = finfo_file($finfo, $filePath);
				finfo_close($finfo);

				if (is_string($detected)) {
					$mime = $detected;
				}
			}
		}

		if ($mime === '' && function_exists('mime_content_type')) {
			$detected = mime_content_type($filePath);
			if (is_string($detected)) {
				$mime = $detected;
			}
		}

		if ($mime === '' && function_exists('getimagesize')) {
			$imageInfo = @getimagesize($filePath);
			if (is_array($imageInfo) && isset($imageInfo['mime']) && is_string($imageInfo['mime'])) {
				$mime = $imageInfo['mime'];
			}
		}

		return strtolower(trim($mime));
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

	private function loadProjectTags(int $projectId): array
	{
		try {
			return (new ProjectTagsModel())->findAllByProjectId($projectId);
		} catch (Throwable) {
			try {
				$tagRelations = (new ProjectsTagsRelationModel())->findAllByProjectId($projectId);
				return array_map(
					static fn($tag) => (object) [
						'id' => (int) ($tag->tag_id ?? $tag->id ?? 0),
						'name' => (string) ($tag->name ?? ''),
					],
					$tagRelations
				);
			} catch (Throwable) {
				return [];
			}
		}
	}
}

