<?php
/** @var \Modules\Main\Template $this */

use Modules\Main\Auth;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$showAdminSidebar = Auth::getInstance()->isAdmin();

$homeActive = $currentPath === '/admin/' ? ' is-active' : '';
$projectsActive = str_starts_with($currentPath, '/admin/projects/') ? ' is-active' : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $this->getParam('title') ?></title>
	<link href="/Templates/Default/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php if ($showAdminSidebar): ?>
	<style>
		.admin-shell {
			min-height: 100vh;
		}

		.admin-sideout {
			position: fixed;
			top: 0;
			left: 0;
			height: 100vh;
			width: 320px;
			background: #ffffff;
			border-right: 1px solid #e9ecef;
			box-shadow: 0 0.2rem 1rem rgba(0, 0, 0, 0.08);
			overflow-y: auto;
			z-index: 1030;
		}

		.admin-sideout__brand {
			padding: 1rem 0.875rem;
			border-bottom: 1px solid #e9ecef;
		}

		.admin-sideout__brand-text {
			opacity: 1;
			transform: none;
			white-space: nowrap;
		}

		.admin-sideout__nav {
			padding: 0.6rem 0;
		}

		.admin-sideout__link {
			display: flex;
			align-items: center;
			gap: 0.75rem;
			padding: 0.62rem 0.95rem;
			color: #495057;
			text-decoration: none;
			transition: background-color 0.15s ease, color 0.15s ease;
		}

		.admin-sideout__link:hover {
			background: #f4f7ff;
			color: #0d6efd;
		}

		.admin-sideout__link.is-active {
			background: #e8f0ff;
			color: #0d6efd;
			font-weight: 600;
		}

		.admin-sideout__icon {
			width: 34px;
			height: 34px;
			border-radius: 9px;
			background: #f1f3f5;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 0.75rem;
			font-weight: 700;
			flex-shrink: 0;
		}

		.admin-sideout__label {
			opacity: 1;
			transform: none;
			white-space: nowrap;
		}

		.admin-main {
			margin-left: 336px;
			min-height: 100vh;
		}

		@media (max-width: 991.98px) {
			.admin-sideout {
				position: static;
				width: auto;
				height: auto;
				margin: 0.75rem;
				border-radius: 14px;
			}

			.admin-sideout__brand-text,
			.admin-sideout__label {
				opacity: 1;
				transform: none;
			}

			.admin-main {
				margin-left: 0;
				padding: 0 0.75rem 0.75rem;
			}
		}
	</style>

	<div class="admin-shell">
		<aside class="admin-sideout" aria-label="Боковое меню">
			<div class="admin-sideout__brand">
				<div class="small fw-semibold admin-sideout__brand-text">Панель администратора</div>
			</div>

			<nav class="admin-sideout__nav">
				<a class="admin-sideout__link<?= $homeActive ?>" href="/admin/">
					<span class="admin-sideout__icon">Гл</span>
					<span class="admin-sideout__label">Главная</span>
				</a>
				<a class="admin-sideout__link<?= $projectsActive ?>" href="/admin/projects/">
					<span class="admin-sideout__icon">Пр</span>
					<span class="admin-sideout__label">Проекты</span>
				</a>
				<a class="admin-sideout__link" href="#">
					<span class="admin-sideout__icon">По</span>
					<span class="admin-sideout__label">Пользователи</span>
				</a>
				<a class="admin-sideout__link" href="#">
					<span class="admin-sideout__icon">На</span>
					<span class="admin-sideout__label">Настройки</span>
				</a>
			</nav>
		</aside>

		<main class="admin-main">
			<div class="container-fluid py-4">
<?php else: ?>
	<main class="container py-5">
<?php endif; ?>
