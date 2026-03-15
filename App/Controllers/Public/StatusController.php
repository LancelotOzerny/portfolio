<?php

namespace Controllers\Public;

use Modules\Main\BaseController;
use Modules\Main\Template;

class StatusController extends BaseController
{
	public function page404() : void
	{
		Template::getInstance()->setParam('title', 'Страница не существует');
		Template::getInstance()->template = 'Default';

		Template::getInstance()->showHeader();
		$this->render('404');
		Template::getInstance()->showFooter();
	}

	public function page500()
	{
		Template::getInstance()->setParam('title', 'Внутренняя ошибка сервера');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('500');
		\Modules\Main\Template::getInstance()->showFooter();
	}
}