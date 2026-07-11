<?php
namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Models\ProjectsModel;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Modules\Main\ViewData;

class PortfolioController extends BaseController
{
    public function index() : void
    {
		$this->setSeo(SeoContext::page('portfolio'));
		$data = [];

		Template::getInstance()->setParam('title', 'Мои работы');
		Template::getInstance()->setParam('subtitle', 'Примеры реализованных задач с описанием технологий и результатов');

		$projectsModel = new ProjectsModel();
		$data['projects'] = $projectsModel->findAll();

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index', $data);
		\Modules\Main\Template::getInstance()->showFooter();
	}

	public function detail(int $id) : void
	{
		$data = [];

		$projectsModel = new ProjectsModel();
		$project = $projectsModel->findById($id);
		$data['info'] = $project;

		if ($project !== null) {
			$this->setSeo(SeoContext::entity(
				'project',
				(string) $id,
				[
					'title' => (string) ($project->name ?? ''),
					'description' => trim(strip_tags((string) ($project->preview_text ?? ''))),
					'og_image' => (string) ($project->preview_image_url ?? ''),
				],
				'/portfolio/projects/' . $id . '/'
			));
		} else {
			$this->setSeo(SeoContext::custom('/portfolio/projects/' . $id . '/', [
				'title' => 'Проект не найден',
				'robots_index' => false,
			]));
		}

		Template::getInstance()->setParam('title', $project !== null ? (string) ($project->name ?? 'Мои работы') : 'Проект не найден');
		Template::getInstance()->setParam('subtitle', 'Примеры реализованных задач с описанием технологий и результатов');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('detail', $data);
		\Modules\Main\Template::getInstance()->showFooter();
	}
}