<?php
/** @var \Modules\Main\Template $this */

use Modules\Main\Auth;

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$showAdminSidebar = Auth::getInstance()->isAdmin();

$homeActive = $currentPath === '/admin/' ? ' is-active' : '';
$contentActive = str_starts_with($currentPath, '/admin/projects/') || str_starts_with($currentPath, '/admin/content/') ? ' is-active' : '';
$projectsActive = str_starts_with($currentPath, '/admin/projects/') ? ' is-active' : '';
$blogActive = str_starts_with($currentPath, '/admin/content/blog/') ? ' is-active' : '';
$blogRubricsActive = str_starts_with($currentPath, '/admin/content/blog/rubrics/') || $currentPath === '/admin/content/blog/' ? ' is-active' : '';
$blogArticlesActive = str_starts_with($currentPath, '/admin/content/blog/articles/') ? ' is-active' : '';
$tagsActive = str_starts_with($currentPath, '/admin/content/tags/') ? ' is-active' : '';
$galleryActive = str_starts_with($currentPath, '/admin/content/gallery/') ? ' is-active' : '';
$usersActive = str_starts_with($currentPath, '/admin/users/') ? ' is-active' : '';
$developmentActive = str_starts_with($currentPath, '/admin/development/') ? ' is-active' : '';
$developmentSqlActive = str_starts_with($currentPath, '/admin/development/sql/') ? ' is-active' : '';
$developmentTodoActive = str_starts_with($currentPath, '/admin/development/todo/') ? ' is-active' : '';
$developmentRepositoryActive = str_starts_with($currentPath, '/admin/development/repository/') ? ' is-active' : '';
$settingsActive = str_starts_with($currentPath, '/admin/settings/') ? ' is-active' : '';
$resumeActive = str_starts_with($currentPath, '/admin/resume/') ? ' is-active' : '';
$seoActive = str_starts_with($currentPath, '/admin/seo/') ? ' is-active' : '';
$configsActive = str_starts_with($currentPath, '/admin/settings/configs/') ? ' is-active' : '';
$templatesActive = str_starts_with($currentPath, '/admin/settings/templates/') ? ' is-active' : '';
$backupActive = str_starts_with($currentPath, '/admin/settings/backup/') ? ' is-active' : '';
$backupCreateActive = str_starts_with($currentPath, '/admin/settings/backup/create/') ? ' is-active' : '';
$backupListActive = str_starts_with($currentPath, '/admin/settings/backup/list/') ? ' is-active' : '';
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
				<a class="admin-sideout__link<?= $homeActive ?>" href="/admin/">
					<span class="admin-sideout__icon">Гл</span>
					<span class="admin-sideout__label">Главная</span>
				</a>

				<div class="admin-sideout__item<?= $contentActive ?>">
					<a class="admin-sideout__link admin-sideout__link--has-flyout<?= $contentActive ?>" href="/admin/projects/">
						<span class="admin-sideout__icon">Кн</span>
						<span class="admin-sideout__label">Контент</span>
					</a>
					<div class="admin-sideout__flyout">
						<a class="admin-sideout__flyout-link<?= $projectsActive ?>" href="/admin/projects/">Проекты</a>
						<div class="admin-sideout__flyout-item<?= $blogActive ?>">
							<a class="admin-sideout__flyout-link admin-sideout__flyout-link--has-flyout<?= $blogActive ?>" href="/admin/content/blog/">Блог</a>
							<div class="admin-sideout__flyout">
								<a class="admin-sideout__flyout-link<?= $blogRubricsActive ?>" href="/admin/content/blog/rubrics/">Рубрики</a>
								<a class="admin-sideout__flyout-link<?= $blogArticlesActive ?>" href="/admin/content/blog/articles/">Статьи</a>
							</div>
						</div>
						<a class="admin-sideout__flyout-link<?= $tagsActive ?>" href="/admin/content/tags/">Теги</a>
						<a class="admin-sideout__flyout-link<?= $galleryActive ?>" href="/admin/content/gallery/">Галерея</a>
					</div>
				</div>

				<a class="admin-sideout__link<?= $seoActive ?>" href="/admin/seo/">
					<span class="admin-sideout__icon">SE</span>
					<span class="admin-sideout__label">SEO</span>
				</a>

				<a class="admin-sideout__link<?= $usersActive ?>" href="/admin/users/">
					<span class="admin-sideout__icon">По</span>
					<span class="admin-sideout__label">Пользователи</span>
				</a>

				<div class="admin-sideout__item<?= $resumeActive ?>">
					<a class="admin-sideout__link admin-sideout__link--has-flyout<?= $resumeActive ?>" href="/admin/resume/experience/">
						<span class="admin-sideout__icon">Рз</span>
						<span class="admin-sideout__label">Резюме</span>
					</a>
					<div class="admin-sideout__flyout">
						<a class="admin-sideout__flyout-link<?= $resumeActive ?>" href="/admin/resume/experience/">Опыт работы</a>
					</div>
				</div>

				<div class="admin-sideout__item<?= $developmentActive ?>">
					<a class="admin-sideout__link admin-sideout__link--has-flyout<?= $developmentActive ?>" href="/admin/development/sql/">
						<span class="admin-sideout__icon">Рд</span>
						<span class="admin-sideout__label">Разработка</span>
					</a>
					<div class="admin-sideout__flyout">
						<a class="admin-sideout__flyout-link<?= $developmentSqlActive ?>" href="/admin/development/sql/">SQL запросы</a>
						<a class="admin-sideout__flyout-link<?= $developmentTodoActive ?>" href="/admin/development/todo/">To Do List</a>
						<a class="admin-sideout__flyout-link<?= $developmentRepositoryActive ?>" href="/admin/development/repository/">Репозиторий</a>
					</div>
				</div>

				<div class="admin-sideout__item<?= $settingsActive ?>">
					<a class="admin-sideout__link admin-sideout__link--has-flyout<?= $settingsActive ?>" href="/admin/settings/">
						<span class="admin-sideout__icon">На</span>
						<span class="admin-sideout__label">Настройки</span>
					</a>
					<div class="admin-sideout__flyout">
						<a class="admin-sideout__flyout-link<?= $configsActive ?>" href="/admin/settings/configs/">Конфиги</a>
						<a class="admin-sideout__flyout-link<?= $templatesActive ?>" href="/admin/settings/templates/">Шаблоны</a>
						<div class="admin-sideout__flyout-item<?= $backupActive ?>">
							<a class="admin-sideout__flyout-link admin-sideout__flyout-link--has-flyout<?= $backupActive ?>" href="/admin/settings/backup/">Резервное копирование</a>
							<div class="admin-sideout__flyout">
								<a class="admin-sideout__flyout-link<?= $backupCreateActive ?>" href="/admin/settings/backup/create/">Создание копии</a>
								<a class="admin-sideout__flyout-link<?= $backupListActive ?>" href="/admin/settings/backup/list/">Список копий</a>
							</div>
						</div>
					</div>
				</div>
			</nav>
		</aside>

		<main class="admin-main">
			<div class="container-fluid py-4">
<?php else: ?>
	<main class="container py-5">
<?php endif; ?>
