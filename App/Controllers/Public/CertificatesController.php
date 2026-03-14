<?php
namespace Controllers\Public;

use Modules\Main\BaseController;
use Modules\Main\Template;
use Modules\Main\ViewData;

class CertificatesController extends BaseController
{
    public function index() : void
    {
		Template::getInstance()->setParam('title', 'Сертификаты и дипломы');
		Template::getInstance()->setParam('subtitle', 'Подтверждённая квалификация: курсы, тренинги и образовательные программы');

		\Modules\Main\Template::getInstance()->showHeader();
		$this->render('index');
		\Modules\Main\Template::getInstance()->showFooter();
	}
}