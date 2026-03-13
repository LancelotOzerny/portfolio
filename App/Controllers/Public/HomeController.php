<?php
namespace Controllers\Public;

use Modules\Main\BaseController;

class HomeController extends BaseController
{
    public function index() : void
    {
        $this->render('index');
    }
}