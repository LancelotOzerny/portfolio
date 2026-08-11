<?php
namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Modules\Main\BaseController;
use Modules\Main\Template;

class CookiesController extends BaseController
{
	public function index(): void
	{
		$this->setSeo(SeoContext::page('cookies'));
		Template::getInstance()->setParam('title', 'Политика куки');
		Template::getInstance()->setParam('subtitle', 'Какие файлы cookie использует сайт и зачем они нужны');
		Template::getInstance()->setParam('show_contact_cta', false);

		Template::getInstance()->showHeader();
		$this->render('index');
		Template::getInstance()->showFooter();
	}
}
