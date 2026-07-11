<?php
namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Modules\Main\ViewData;

class AboutController extends BaseController
{
    public function index() : void
    {
		$this->setSeo(SeoContext::page('about'));
		Template::getInstance()->setParam('title', 'Знакомьтесь: Максим Беляков');
		Template::getInstance()->setParam('subtitle', 'Кто я, чем занимаюсь и что мне действительно важно в работе');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index');
		\Modules\Main\Template::getInstance()->showFooter();
	}
}