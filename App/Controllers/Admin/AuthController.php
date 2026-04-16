<?php

namespace Controllers\Admin;

use Modules\Main\BaseController;
use Modules\Main\Template;

class AuthController extends BaseController
{
    public function login(): void
    {
        Template::getInstance()->template = 'Admin';
        Template::getInstance()->setParam('title', 'Страница авторизации');

        Template::getInstance()->showHeader();
        $this->render('login');
        Template::getInstance()->showFooter();
    }
}
