<?php
namespace Modules\Main;

class Template
{
	public string $template = 'Default';
	public readonly string $templateFolderPath;
	public array $params = [];

	public function __construct(?string $templateFolderPath = null)
	{
		$this->templateFolderPath = App::getInstance()->root . '/Develop/Templates';
	}

	public function setParam(string $key, $value): self
	{
		$this->params[$key] = $value;
		return $this;
	}

	public function getParam(string $key)
	{
		return $this->params[$key] ?? null;
	}

	public function showHeader(): void
	{
		$filePath = "$this->templateFolderPath/$this->template/header.php";

		if (!file_exists($filePath)) {
			echo "Header of template '$this->template' is not founded!";
			return;
		}

		include $filePath;
	}

	public function showFooter(): void
	{
		$filePath = "$this->templateFolderPath/$this->template/footer.php";

		if (!file_exists($filePath)) {
			echo "Footer of template '$this->template' is not founded!";
			return;
		}

		include $filePath;
	}
}