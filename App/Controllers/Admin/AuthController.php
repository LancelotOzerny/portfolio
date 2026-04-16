<?php

namespace Controllers\Admin;

use Modules\Main\BaseController;
use Modules\Main\Template;

class AuthController extends BaseController
{
    public function login(): void
    {
        Template::getInstance()->setParam('title', 'Admin Login');

        Template::getInstance()->showHeader();
        $this->render('login');
        Template::getInstance()->showFooter();
    }
}
