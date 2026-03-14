<?php
$defaultTemplatePath = '/Templates/Default';
define('DEFAULT_TEMPLATE_PATH', \Modules\Main\App::getInstance()->root . '/public_html' . $defaultTemplatePath);

require_once DEFAULT_TEMPLATE_PATH . '/header.php';
?>

<div class="text-center py-5 bg-gradient"
     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
	<h1 class="display-3 fw-light fs-1"><?= $this->getParam('title') ?></h1>
	<p class="lead fs-4"><?= $this->getParam('subtitle') ?></p>
</div>
