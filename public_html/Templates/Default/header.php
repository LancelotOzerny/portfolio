<?php
/** @param \Modules\Main\Template $this */
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->getParam('title') ?></title>
    <link href="/Templates/Default/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Templates/Default/css/styles.css" rel="stylesheet">
</head>
<body>
<?php
(new \Components\AdminBar\AdminBar())->render();
<!-- NAVIGATION -->
<?php
(new \Components\Navigation\Navigation(['type' => 'main']))->render();
?>

<div class="container-fluid">
