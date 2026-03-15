<?php

namespace Modules\Main;

class BaseComponent
{
    protected array $params = [];

    final public function __construct(array $params = [])
    {
           $this->prepareData($params);
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

    final public function render() : void
    {
        $reflection = new \ReflectionClass(static::class);
        $classFile = $reflection->getFileName();
        $classPath = dirname($classFile);

        $template = $this->getParam('template') ?? 'Default';
        $viewPath = "{$classPath}/{$template}/index.php";
        $viewPath = str_replace('\\', '/', $viewPath);

        if (file_exists($viewPath))
        {
            include $viewPath;
            return;
        }

        echo "Template `{$template}` of component `{$classPath}` not found<br/>";
    }
}