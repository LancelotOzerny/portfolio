<?php

namespace Modules\Main;

class App
{
    public readonly string $root;
    private static ?self $instance = null;

    private function __construct()
    {
        $this->root = dirname($_SERVER['DOCUMENT_ROOT']);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
}