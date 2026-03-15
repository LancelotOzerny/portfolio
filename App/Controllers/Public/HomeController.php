<?php
namespace Controllers\Public;

use Models\ProjectsModel;
use Modules\Main\BaseController;
use Modules\Main\Template;

class HomeController extends BaseController
{
    public function index() : void
    {
		Template::getInstance()->setParam('title', 'Максим Беляков: Портфолио WEB-Разработчика');

		$data = [];
		$data['projects'] = (new ProjectsModel())->findAll();

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index', $data);
		\Modules\Main\Template::getInstance()->showFooter();
	}
}