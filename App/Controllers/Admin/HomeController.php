<?php

namespace Controllers\Admin;

use Modules\Main\BaseController;
use Modules\Main\Template;

class HomeController extends BaseController
{
    public function index(): void
    {
        Template::getInstance()->setParam('title', 'Admin Home');

        Template::getInstance()->showHeader();
        $this->render('index');
        Template::getInstance()->showFooter();
    }
}
