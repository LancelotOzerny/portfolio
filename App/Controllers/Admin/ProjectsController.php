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

		Template::getInstance()->setParam('title', 'Проекты');

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

		Template::getInstance()->setParam('title', 'Редактирование проекта #' . $id);

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

		if ($name === '') {
			header('Location: /admin/projects/' . $id . '/?error=' . rawurlencode('Введите название проекта.'));
			return;
		}

		try {
			if (!$projectsModel->updateMainInfo($id, $name, $active)) {
				throw new \RuntimeException('Unable to update project');
			}

			header('Location: /admin/projects/' . $id . '/?saved=1');
			return;
		} catch (Throwable) {
			header('Location: /admin/projects/' . $id . '/?error=' . rawurlencode('Не удалось сохранить изменения.'));
			return;
		}
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
