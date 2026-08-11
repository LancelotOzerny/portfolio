<?php
/** @var \Modules\Main\Template $this */

use App\Services\Admin\Menu\AdminMenuService;
use Modules\Main\Auth;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$showAdminSidebar = Auth::getInstance()->isAdmin();
$adminMenuHtml = $showAdminSidebar
	? (new AdminMenuService())->renderNav($currentPath)
	: '';
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
			width: 260px;
			background: #ffffff;
			border-right: 1px solid #e9ecef;
			box-shadow: 0 0.2rem 1rem rgba(0, 0, 0, 0.08);
			overflow: visible;
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

		.admin-sideout__item {
			position: relative;
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

		.admin-sideout__link.is-active,
		.admin-sideout__item.is-active > .admin-sideout__link {
			background: #e8f0ff;
			color: #0d6efd;
			font-weight: 600;
		}

		.admin-sideout__link--has-flyout::after {
			content: "›";
			margin-left: auto;
			font-size: 1.1rem;
			line-height: 1;
			opacity: 0.45;
		}

		.admin-sideout__flyout {
			position: absolute;
			top: 0;
			left: 100%;
			min-width: 210px;
			padding: 0.45rem 0;
			background: #ffffff;
			border: 1px solid #e9ecef;
			border-radius: 10px;
			box-shadow: 0 0.35rem 1.25rem rgba(0, 0, 0, 0.1);
			opacity: 0;
			visibility: hidden;
			pointer-events: none;
			transition: opacity 0.15s ease, visibility 0.15s ease;
			z-index: 1040;
		}

		.admin-sideout__flyout::before {
			content: "";
			position: absolute;
			top: 0;
			right: 100%;
			width: 14px;
			height: 100%;
		}

		.admin-sideout__flyout .admin-sideout__flyout {
			z-index: 1050;
		}

		.admin-sideout__item:hover > .admin-sideout__flyout,
		.admin-sideout__flyout-item:hover > .admin-sideout__flyout {
			opacity: 1;
			visibility: visible;
			pointer-events: auto;
		}

		.admin-sideout__flyout-item {
			position: relative;
			width: 100%;
		}

		.admin-sideout__flyout-link {
			display: flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.52rem 20px;
			color: #6c757d;
			text-decoration: none;
			white-space: nowrap;
			transition: color 0.15s ease, transform 0.15s ease;
		}

		.admin-sideout__flyout-link:hover {
			color: #0d6efd;
			transform: translateX(10px);
		}

		.admin-sideout__flyout-link.is-active {
			color: #0d6efd;
			font-weight: 600;
		}

		.admin-sideout__flyout-link--has-flyout::after {
			content: "›";
			margin-left: auto;
			font-size: 1.05rem;
			line-height: 1;
			opacity: 0.45;
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
			margin-left: 276px;
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

			.admin-sideout__flyout {
				position: static;
				min-width: 0;
				margin: 0 0.75rem 0.35rem 2.75rem;
				padding: 0.25rem 0;
				border: none;
				border-left: 2px solid #e9ecef;
				border-radius: 0;
				box-shadow: none;
				opacity: 1;
				visibility: visible;
				pointer-events: auto;
			}

			.admin-sideout__flyout::before {
				display: none;
			}

			.admin-sideout__flyout-link {
				padding: 0.42rem 20px;
			}

			.admin-sideout__flyout .admin-sideout__flyout {
				margin-left: 1.25rem;
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
				<?= $adminMenuHtml ?>
			</nav>
		</aside>

		<main class="admin-main">
			<div class="container-fluid py-4">
<?php else: ?>
	<main class="container py-5">
<?php endif; ?>
