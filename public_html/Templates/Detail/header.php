<?php
$defaultTemplatePath = '/Templates/Default';
define('DEFAULT_TEMPLATE_PATH', \Modules\Main\App::getInstance()->root . '/public_html' . $defaultTemplatePath);

require_once DEFAULT_TEMPLATE_PATH . '/header.php';
?>