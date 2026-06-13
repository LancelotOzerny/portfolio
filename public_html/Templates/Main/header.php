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
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="/Templates/Main/styles.css">
</head>
<body>
<?php
(new \Components\Navigation\Navigation([
    'type' => 'Main',
    'template' => 'Main'
]))->render();
?>
<div class="container fullscreen d-flex flex-center">
    <div class="profile">
        <div class="profile__image">
            <img src="/upload/images/main/profile.png">
        </div>
        <div class="profile__name">Максим Беляков</div>
        <div class="profile__jobname">PHP\Bitrix-разработчик</div>

        <!-- Компонент -->
        <div class="profile__networks-line"></div>
    </div>
</div>
