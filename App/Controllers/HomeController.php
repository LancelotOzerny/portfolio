<?php
namespace Controllers;

use Develop\Modules\DBWork\Installer;
use http\Params;
use Modules\DBWork\DBConnection;
use Modules\DBWork\QueryBuilder;
use Modules\Main\Config;
use Modules\Main\ConfigObject;

class HomeController
{
    public function index() : void
    {
        echo 'Home Page';
    }
}