<?php
namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Modules\Main\BaseController;
use Modules\Main\Template;
use Modules\Main\ViewData;

class ContactsController extends BaseController
{
    public function index() : void
    {
		$this->setSeo(SeoContext::page('contacts'));
		Template::getInstance()->setParam('title', 'На связи!');
		Template::getInstance()->setParam('subtitle', 'Буду рад ответить на ваши вопросы или обсудить возможное сотрудничество');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index');
		\Modules\Main\Template::getInstance()->showFooter();
	}
}