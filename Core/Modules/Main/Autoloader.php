<?php

namespace Modules\Main;

class Autoloader
{
    private array $namespaces = [];
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(): void
    {
        spl_autoload_register([self::class, 'autoloadClass']);
    }

    public function addPath(string $namespace, string $path): void
    {
        $prefix = trim($namespace, '\\') . '\\';
        $baseDir = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!isset($this->namespaces[$prefix])) {
            $this->namespaces[$prefix] = [];
        }
        $this->namespaces[$prefix][] = $baseDir;
    }

    public static function autoloadClass(string $class): void
    {
        $loader = self::getInstance();

        foreach ($loader->namespaces as $prefix => $baseDirs) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            $relativeClass = substr($class, $len);

            foreach ($baseDirs as $baseDir) {
                $file = $baseDir
                    . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass)
                    . '.php';

                if (is_file($file)) {
                    require $file;
                    return;
                }
            }
        }
    }
}