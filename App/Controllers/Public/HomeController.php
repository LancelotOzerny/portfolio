<?php
namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Models\ProjectsModel;
use Modules\Main\BaseController;
use Modules\Main\Template;

class HomeController extends BaseController
{
    public function index() : void
    {
		$this->setSeo(SeoContext::page('home'));

		$data = [];
		$data['projects'] = (new ProjectsModel())->findAll();

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index', $data);
		\Modules\Main\Template::getInstance()->showFooter();
	}
}