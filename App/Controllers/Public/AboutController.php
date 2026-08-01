<?php
namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Modules\Main\BaseController;
use Modules\Main\Template;

class AboutController extends BaseController
{
	public function index() : void
	{
		$this->setSeo(SeoContext::page('about'));
		Template::getInstance()->setParam('title', 'О себе');
		Template::getInstance()->setParam('subtitle', 'Кто я, чем занимаюсь и что мне действительно важно в работе');

		Template::getInstance()->showHeader();
		$this->render('index');
		Template::getInstance()->showFooter();
	}
}
