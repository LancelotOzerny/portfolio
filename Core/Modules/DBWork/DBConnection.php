<?php

namespace Modules\DBWork;

use Modules\Main\Config;

class DBConnection
{
    private static \PDO|null $connection = null;

    private function __construct() {}

    private function __clone() {}

    public static function getConnection()
    {
		$connectionData = Config::getInstance()->get('Hidden', 'database');
		$dbname = $connectionData->dbname ?? '';
		$login = $connectionData->login ?? 'root';
		$password = $connectionData->password ?? '';
		$host = $connectionData->host ?? 'localhost';
		$charset = $connectionData->charset ?? 'utf8';

        if (!self::$connection)
		{
            self::$connection = new \PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $login, $password);
        }

        return self::$connection;
    }
}