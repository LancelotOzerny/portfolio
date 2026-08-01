<?php
namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Modules\Main\BaseController;
use Modules\Main\Template;

class ContactsController extends BaseController
{
	public function index() : void
	{
		$this->setSeo(SeoContext::page('contacts'));
		Template::getInstance()->setParam('title', 'Связаться со мной');
		Template::getInstance()->setParam('subtitle', 'Буду рад ответить на ваши вопросы или обсудить возможное сотрудничество');
		Template::getInstance()->setParam('show_contact_cta', false);

		Template::getInstance()->showHeader();
		$this->render('index');
		Template::getInstance()->showFooter();
	}
}
