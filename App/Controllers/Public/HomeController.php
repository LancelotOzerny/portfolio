<?php
namespace Controllers\Public;

use Modules\Main\BaseController;
use Modules\Main\Template;

class HomeController extends BaseController
{
    public function index() : void
    {
		$template = new Template();

		$template
			->setParam('title', 'Test Title')
			->setParam('header', 'Test Header');

		$template->showHeader();
        $this->render('index');
		$template->showFooter();
    }
}