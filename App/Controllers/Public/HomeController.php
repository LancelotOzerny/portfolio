<?php
namespace Controllers\Public;

use Modules\Main\BaseController;
use Modules\Main\ViewData;

class HomeController extends BaseController
{
    public function index() : void
    {
		ViewData::getInstance()
			->set('title', 'Test Title');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index');
		\Modules\Main\Template::getInstance()->showFooter();
	}
}