<?php

namespace Modules\Main;

class BaseComponent
{
    protected array $params = [];

    final public function __construct(array $params = [])
    {
		$this->prepareData($params);

		if (isset($params['template']))
		{
			$this->setParam('template', $params['template']);
		}
		else
		{
			$this->setParam('template', 'Default');
		}
	}

    protected function prepareData(array $params = []) : void
    {
        $this->params = $params;
    }

    final public function setParam(string $key, $value) : static
    {
        $this->params[$key] = $value;
        return $this;
    }

    final public function getParam(string $key)
    {
        return $this->params[$key] ?? null;
    }

	final public function hasParam(string $key) : bool
	{
		return array_key_exists($key, $this->params);
	}

    final public function render() : void
    {
		$classPath = $this->getClassPath();
		$template = $this->getParam('template');
        $templatePath = "{$classPath}/{$template}";
		$templatePath = str_replace('\\', '/', $templatePath);
		$viewPath = $templatePath . '/index.php';

		if (file_exists($path = $templatePath . '/script.js'))
		{
			$path = str_replace(App::getInstance()->root . '/public_html', '', $path);
			AssetLoader::getInstance()->addJs($path);
		}

		if (file_exists($path = $templatePath . '/styles.css'))
		{
			$path = str_replace(App::getInstance()->root . '/public_html', '', $path);
			AssetLoader::getInstance()->addCss($path);
		}

        if (file_exists($viewPath))
        {
            include $viewPath;
            return;
        }

        echo "Template `{$template}` of component `{$classPath}` not found<br/>";
    }

	final protected function getClassPath() : string
	{
		$reflection = new \ReflectionClass(static::class);
		$classFile = $reflection->getFileName();
		$classPath = dirname($classFile);

		return $classPath;
	}
}