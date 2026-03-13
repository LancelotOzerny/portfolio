<?php

namespace Modules\Main;

class Config
{
	protected static ?self $instance = null;
	protected array $cache = [];
	protected string $configDir;

	private function __construct()
	{
		$this->configDir = App::getInstance()->root . '/App/Configs';
	}

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get(string $section, ?string $file = null): array|object
	{
		if ($file)
		{
			$key = "$section/$file";
			if (!isset($this->cache[$key]))
			{
				$path = $this->configDir . "/$section/$file.php";
				$this->cache[$key] = $this->loadFile($path);
			}

			return new ConfigObject($this->cache[$key]);
		}

		$key = $section;
		if (!isset($this->cache[$key])) {
			$dir = $this->configDir . "/$section";
			$files = glob("$dir/*.php");
			$data = [];
			foreach ($files as $file) {
				$name = basename($file, '.php');
				$data[$name] = $this->loadFile($file);
			}
			$this->cache[$key] = $data;
		}
		return new ConfigObject($this->cache[$key]);
	}

	public function clear(): void
	{
		$this->cache = [];
	}

	protected function loadFile(string $path): array
	{
		if (!file_exists($path)) {
			return [];
		}
		$config = require $path;
		return is_array($config) ? $config : [];
	}
}