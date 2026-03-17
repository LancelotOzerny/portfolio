<?php

namespace Modules\Main;

class AssetLoader
{
	private static ?self $instance = null;
	private array $css = [];
	private array $js = [];

	public function addCss(string $path): void
	{
		if (in_array($path, $this->css)) {
			return;
		}

		$this->css[] = $path;
	}

	public function addJs(string $path): void
	{
		if (in_array($path, $this->js)) {
			return;
		}

		$this->js[] = $path;
	}

	public function getCssLines(): string
	{
		$result = '';

		foreach ($this->css as $path) {
			$result .= '<link rel="stylesheet" href="' . $path . '">';
		}

		return $result;
	}

	public function getJsLines(): string
	{
		$result = '';
		foreach ($this->js as $path)
		{
			$result .= '<script src="' . $path . '"></script>';
		}
		return $result;
	}

	private function __construct() {}
	private function __clone() {}

	public static function getInstance(): ?AssetLoader
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}
}