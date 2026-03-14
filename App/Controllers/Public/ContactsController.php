<?php
namespace Controllers\Public;

use Modules\Main\BaseController;
use Modules\Main\Template;
use Modules\Main\ViewData;

class ContactsController extends BaseController
{
    public function index() : void
    {
		Template::getInstance()->setParam('title', 'На связи!');
		Template::getInstance()->setParam('subtitle', 'Буду рад(а) ответить на ваши вопросы или обсудить возможное сотрудничество');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index');
		\Modules\Main\Template::getInstance()->showFooter();
	}
}