<?php

namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Modules\Main\BaseController;
use Modules\Main\Template;

class StatusController extends BaseController
{
	public function page404() : void
	{
		$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
		$this->setSeo(SeoContext::custom($path, [
			'title' => 'Страница не существует',
			'robots_index' => false,
		]));
		Template::getInstance()->setParam('title', 'Страница не существует');
		Template::getInstance()->template = 'Default';

		Template::getInstance()->showHeader();
		$this->render('404');
		Template::getInstance()->showFooter();
	}

	public function page500()
	{
		$this->setSeo(SeoContext::custom('/', [
			'title' => 'Внутренняя ошибка сервера',
			'robots_index' => false,
		]));
		Template::getInstance()->setParam('title', 'Внутренняя ошибка сервера');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('500');
		\Modules\Main\Template::getInstance()->showFooter();
	}
}