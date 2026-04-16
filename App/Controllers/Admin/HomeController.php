<?php

namespace Controllers\Admin;

use Modules\Main\BaseController;
use Modules\Main\Auth;
use Modules\Main\Template;

class HomeController extends BaseController
{
    public function index(): void
    {
		$auth = Auth::getInstance();
		if ($auth->getCurrentUser() === null || !$auth->isAdmin()) {
			header('Location: /admin/login/');
			return;
		}

        Template::getInstance()->setParam('title', 'Панель администратора');

        Template::getInstance()->showHeader();
        $this->render('index');
        Template::getInstance()->showFooter();
    }
}
