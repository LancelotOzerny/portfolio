<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title><?= $this->getParam('title') ?></title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800;900&display=swap" rel="stylesheet">
	<link href="/Templates/Default/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="/Templates/Inner/styles.css">
</head>
<body class="inner-page">
<?php
(new \Components\AdminBar\AdminBar())->render();
(new \Components\Navigation\Navigation([
	'type' => 'Main',
	'template' => 'Main'
]))->render();
?>

<section class="inner-hero">
	<div class="container inner-hero__container">
		<div class="inner-hero__content">
			<a class="inner-hero__back" href="/">LANCY</a>
			<h1 class="inner-hero__title"><?= $this->getParam('title') ?></h1>
			<?php if ($this->getParam('subtitle')): ?>
				<p class="inner-hero__subtitle"><?= $this->getParam('subtitle') ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
