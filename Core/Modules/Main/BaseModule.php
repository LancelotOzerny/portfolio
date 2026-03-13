<?php

namespace Modules\Main;

abstract class BaseModule
{
	public function __construct() {}

	public abstract function install() : void;
    protected function installFiles() : void {}
    protected function installDatabase() : void {}

    protected function getName() : string
    {
        return 'No Named';
    }
    protected function getDescription() : string
    {
        return 'No Description';
    }
    protected function getVersion() : string
    {
        return '1.0.0';
    }

    function recursiveCopy(string $source, string $dest): bool
    {
        if (!is_dir($dest))
        {
            mkdir($dest, 0755, true);
        }

        $files = scandir($source);

        if ($files === false)
        {
            return false;
        }

        foreach ($files as $file)
        {
            if ($file === '.' || $file === '..')
            {
                continue;
            }

            $srcPath = $source . '/' . $file;
            $dstPath = $dest . '/' . $file;

            if (is_dir($srcPath))
            {
                $this->recursiveCopy($srcPath, $dstPath);
            }
            else
            {
                copy($srcPath, $dstPath);
            }
        }

        return true;
    }
}