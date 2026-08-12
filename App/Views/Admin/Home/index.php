<?php
/* @var array $data */

$projectsCount = (int) ($data['projectsCount'] ?? 0);
$usersCount = (int) ($data['usersCount'] ?? 0);
$articlesCount = (int) ($data['articlesCount'] ?? 0);
$blogViewsWeekCount = (int) ($data['blogViewsWeekCount'] ?? 0);
$blogViewsMonthCount = (int) ($data['blogViewsMonthCount'] ?? 0);
$blogCommentsMonthCount = (int) ($data['blogCommentsMonthCount'] ?? 0);

$renderInfoBlock = static function (string $iconSrc, string $label, string $value): void {
	?>
	<div class="admin-dashboard__block">
		<img
			class="admin-dashboard__block-icon"
			src="<?= htmlspecialchars($iconSrc) ?>"
			width="128"
			height="128"
			alt=""
		>
		<div class="admin-dashboard__block-label"><?= htmlspecialchars($label) ?></div>
		<div class="admin-dashboard__block-value"><?= htmlspecialchars($value) ?></div>
	</div>
	<?php
};

$renderSectionTitle = static function (string $title): void {
	?>
	<h3 class="admin-dashboard__section-title">
		<span class="admin-dashboard__section-title-line" aria-hidden="true"></span>
		<span class="admin-dashboard__section-title-text"><?= htmlspecialchars($title) ?></span>
		<span class="admin-dashboard__section-title-line" aria-hidden="true"></span>
	</h3>
	<?php
};

$renderStatCard = static function (string $label, string $value, string $valueClass = 'h3'): void {
	?>
	<div class="col-12 col-sm-6 col-xl-3">
		<div class="card border-0 shadow-sm h-100">
			<div class="card-body">
				<p class="small text-uppercase text-secondary mb-1"><?= htmlspecialchars($label) ?></p>
				<p class="<?= htmlspecialchars($valueClass) ?> mb-0"><?= htmlspecialchars($value) ?></p>
			</div>
		</div>
	</div>
	<?php
};

$iconBasePath = '/Templates/Admin/images/dashboard/';
?>

<section class="admin-dashboard">
	<style>
		.admin-dashboard__section {
			padding-bottom: 0;
		}

		.admin-dashboard__section + .admin-dashboard__section {
			padding-top: 0px;
		}

		.admin-dashboard__section-title {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 1.25rem;
			font-size: 1.125rem;
			font-weight: 600;
			line-height: 1.2;
			text-transform: uppercase;
			letter-spacing: 0.12em;
			color: #495057;
            padding: 100px 0 50px 0;
		}

		.admin-dashboard__section-title-line {
			flex: 1 1 0;
			max-width: 120px;
			height: 1px;
			background: linear-gradient(to right, transparent, #ced4da 20%, #ced4da 80%, transparent);
		}

		.admin-dashboard__section-title-text {
			flex: 0 0 auto;
			padding: 0.35rem 1rem;
			border-top: 1px solid #dee2e6;
			border-bottom: 1px solid #dee2e6;
		}

		.admin-dashboard__blocks {
			display: flex;
			justify-content: center;
			align-items: stretch;
			width: 100%;
		}

		.admin-dashboard__block {
			flex: 1 1 0;
			min-width: 0;
            max-width: 350px;
			display: flex;
			flex-direction: column;
			align-items: center;
			text-align: center;
			padding: 0 25px;
		}

		.admin-dashboard__block:not(:last-child) {
			border-right: 1px solid #dee2e6;
		}

		.admin-dashboard__block-icon {
			display: block;
			width: 128px;
			height: 128px;
			max-width: 100%;
			object-fit: contain;
			margin-bottom: 12.5px;
		}

		.admin-dashboard__block-label {
			font-size: 1.09375rem;
			font-weight: 600;
			line-height: 1.3;
			text-transform: uppercase;
			letter-spacing: 0.04em;
			color: #495057;
			margin-bottom: 12.5px;
		}

		.admin-dashboard__block-value {
			font-size: 1.75rem;
			font-weight: 600;
			line-height: 1.2;
			color: #212529;
		}
	</style>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body p-4">
			<h2 class="h4 mb-2">Главная</h2>
			<p class="text-secondary mb-0">Используйте меню слева для перехода по разделам админ-панели.</p>
		</div>
	</div>

	<div class="admin-dashboard__section">
		<?php $renderSectionTitle('Продвижение'); ?>
		<div class="admin-dashboard__blocks">
			<?php $renderInfoBlock($iconBasePath . 'projects.svg', 'Проекты', (string) $projectsCount); ?>
			<?php $renderInfoBlock($iconBasePath . 'users.svg', 'Пользователи', (string) $usersCount); ?>
			<?php $renderInfoBlock($iconBasePath . 'articles.svg', 'Статьи', (string) $articlesCount); ?>
		</div>
	</div>

	<div class="admin-dashboard__section">
		<?php $renderSectionTitle('Блог'); ?>
		<div class="admin-dashboard__blocks">
			<?php $renderInfoBlock($iconBasePath . 'views-week.svg', 'Просмотры за неделю', (string) $blogViewsWeekCount); ?>
			<?php $renderInfoBlock($iconBasePath . 'views-month.svg', 'Просмотры за месяц', (string) $blogViewsMonthCount); ?>
			<?php $renderInfoBlock($iconBasePath . 'comments-month.svg', 'Комментариев за месяц', (string) $blogCommentsMonthCount); ?>
		</div>
	</div>

	<div class="admin-dashboard__section">
		<?php $renderSectionTitle('Отладка'); ?>
		<div class="row g-3">
			<?php $renderStatCard('Info', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('Debug', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('Warning', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('Danger', 'Скоро', 'h5'); ?>
		</div>
	</div>

	<div class="admin-dashboard__section">
		<?php $renderSectionTitle('Посещаемость'); ?>
		<div class="row g-3">
			<?php $renderStatCard('Сегодня', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('На этой неделе', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('За месяц', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('За год', 'Скоро', 'h5'); ?>
		</div>
	</div>
</section>
