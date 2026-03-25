<?php

namespace Modules\Main;

class App
{
    public readonly string $root;
    public readonly string $page;
    private static ?self $instance = null;

    private function __construct()
    {
        $this->root = dirname($_SERVER['DOCUMENT_ROOT']);
		$this->page = $_SERVER['REQUEST_URI'];
    }

    public static function getInstance(): self
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

	public function init() : void
	{
		$this->loadRoutes();
	}

	protected function loadRoutes() : void
	{
		$folder = $this->root . '/App/Routes';
		$files = scandir($folder);

		foreach ($files as $file)
		{
			if (str_ends_with($file, '.php'))
			{
				require_once "{$folder}/{$file}";
			}
		}
	}
}