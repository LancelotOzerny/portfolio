<?php
/** @var \Modules\Main\Template $this */
?>
<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php include \Modules\Main\App::getInstance()->root . '/public_html/Templates/Shared/seo.php'; ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="/Templates/Light/styles.css">
</head>
<body class="light-page">
<?php
(new \Components\AdminBar\AdminBar())->render();
(new \Components\Navigation\Navigation([
	'type' => 'Main',
	'template' => 'College',
]))->render();
?>

<main class="light-page__main">
