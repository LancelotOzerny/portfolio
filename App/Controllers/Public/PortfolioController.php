<?php
namespace Controllers\Public;

use Modules\Main\BaseController;
use Modules\Main\Template;
use Modules\Main\ViewData;

class PortfolioController extends BaseController
{
    public function index() : void
    {
		Template::getInstance()->setParam('title', 'Мои работы');
		Template::getInstance()->setParam('subtitle', 'Примеры реализованных задач с описанием технологий и результатов');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index');
		\Modules\Main\Template::getInstance()->showFooter();
	}
}