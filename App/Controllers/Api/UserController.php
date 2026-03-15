<?php
namespace Controllers\Api;

use Models\UsersModel;
use Modules\Main\Config;
use Modules\DBWork\DBConnection;
use Modules\DBWork\QueryBuilder;

class UserController
{
	function __construct()
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
		header('Access-Control-Allow-Headers: *');
	}

	function getAll() : void
	{
		$userModel = new UsersModel();
		$users = $userModel->findAll();

		echo json_encode($users);
	}
}