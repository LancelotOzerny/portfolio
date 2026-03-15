<?php
namespace Controllers\Public;

use Models\ProjectsModel;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Modules\Main\ViewData;

class PortfolioController extends BaseController
{
    public function index() : void
    {
		$data = [];

		Template::getInstance()->setParam('title', 'Мои работы');
		Template::getInstance()->setParam('subtitle', 'Примеры реализованных задач с описанием технологий и результатов');

		$projectsModel = new ProjectsModel();
		$data['projects'] = $projectsModel->findAll();

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index', $data);
		\Modules\Main\Template::getInstance()->showFooter();
	}
}