<?php
namespace Develop\Modules\DBWork;

use Modules\Main\App;

class Installer extends \Modules\Main\BaseModule
{
	public function install(): void
    {
        $this->installDatabase();
        $this->installFiles();
    }

    protected function installFiles(): void
    {
		/* CLASSES */
        $this->recursiveCopy(
			App::getInstance()->root . '/Develop/Modules/DBWork/Classes',
			App::getInstance()->root . '/Core/Modules/DBWork'
		);

		/* CONFIG */
        $this->recursiveCopy(
			App::getInstance()->root . '/Develop/Modules/DBWork/Configs',
			App::getInstance()->root . '/App/Configs/'
		);
    }
}