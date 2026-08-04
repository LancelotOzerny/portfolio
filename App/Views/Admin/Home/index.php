<?php
/* @var array $data */

$projectsCount = (int) ($data['projectsCount'] ?? 0);
$usersCount = (int) ($data['usersCount'] ?? 0);
$rubricsCount = (int) ($data['rubricsCount'] ?? 0);
$articlesCount = (int) ($data['articlesCount'] ?? 0);

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
?>

<section class="admin-dashboard">
	<style>
		.admin-dashboard__section {
			padding: 25px 0;
		}
	</style>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body p-4">
			<h2 class="h4 mb-2">Главная</h2>
			<p class="text-secondary mb-0">Используйте меню слева для перехода по разделам админ-панели.</p>
		</div>
	</div>

	<div class="admin-dashboard__section">
		<h3 class="h6 text-uppercase text-secondary mb-3">Продвижение</h3>
		<div class="row g-3">
			<?php $renderStatCard('Проекты', (string) $projectsCount); ?>
			<?php $renderStatCard('Пользователи', (string) $usersCount); ?>
			<?php $renderStatCard('Рубрики', (string) $rubricsCount); ?>
			<?php $renderStatCard('Статьи', (string) $articlesCount); ?>
		</div>
	</div>

	<div class="admin-dashboard__section">
		<h3 class="h6 text-uppercase text-secondary mb-3">Отладка</h3>
		<div class="row g-3">
			<?php $renderStatCard('Info', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('Debug', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('Warning', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('Danger', 'Скоро', 'h5'); ?>
		</div>
	</div>

	<div class="admin-dashboard__section">
		<h3 class="h6 text-uppercase text-secondary mb-3">Посещаемость</h3>
		<div class="row g-3">
			<?php $renderStatCard('Сегодня', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('На этой неделе', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('За месяц', 'Скоро', 'h5'); ?>
			<?php $renderStatCard('За год', 'Скоро', 'h5'); ?>
		</div>
	</div>
</section>
