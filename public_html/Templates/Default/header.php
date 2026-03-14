<?php
/** @param \Modules\Main\Template $this */
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->getParam('title') ?></title>
    <link href="<?= $this->localPath ?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $this->localPath ?>/css/styles.css" rel="stylesheet">
</head>
<body>
<header class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="/">LANCY</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="/">Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about/">О себе</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/portfolio/">Портфолио</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/contacts/">Контакты</a>
                </li>
            </ul>
        </div>
    </div>
</header>
<div class="container-fluid">
