<?php
/* @var array $data */

$projectsCount = (int) ($data['projectsCount'] ?? 0);
$usersCount = (int) ($data['usersCount'] ?? 0);
$rubricsCount = (int) ($data['rubricsCount'] ?? 0);
$articlesCount = (int) ($data['articlesCount'] ?? 0);
$blogViewsWeekCount = (int) ($data['blogViewsWeekCount'] ?? 0);
$blogViewsMonthCount = (int) ($data['blogViewsMonthCount'] ?? 0);
$topArticlesWeek = is_array($data['topArticlesWeek'] ?? null) ? $data['topArticlesWeek'] : [];
$topArticlesMonth = is_array($data['topArticlesMonth'] ?? null) ? $data['topArticlesMonth'] : [];
$topArticlesAllTime = is_array($data['topArticlesAllTime'] ?? null) ? $data['topArticlesAllTime'] : [];
$topRubricWeek = is_object($data['topRubricWeek'] ?? null) ? $data['topRubricWeek'] : null;
$topRubricMonth = is_object($data['topRubricMonth'] ?? null) ? $data['topRubricMonth'] : null;
$topRubricAllTime = is_object($data['topRubricAllTime'] ?? null) ? $data['topRubricAllTime'] : null;

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

$renderArticlesTopTable = static function (string $title, array $items): void {
	?>
	<div class="col-12 col-xl-4">
		<div class="card border-0 shadow-sm h-100">
			<div class="card-body">
				<h5 class="h6 mb-3"><?= htmlspecialchars($title) ?></h5>
				<?php if ($items === []): ?>
					<p class="text-secondary small mb-0">Нет данных.</p>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-sm align-middle mb-0">
							<thead class="table-light">
								<tr>
									<th scope="col" style="width: 2.5rem;">#</th>
									<th scope="col">Статья</th>
									<th scope="col" class="text-end" style="width: 5rem;">Просмотры</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($items as $index => $item): ?>
									<?php
									$articleId = (int) ($item->id ?? 0);
									$articleTitle = trim((string) ($item->title ?? ''));
									$viewsCount = (int) ($item->views_count ?? 0);
									?>
									<tr>
										<td class="text-secondary"><?= $index + 1 ?></td>
										<td>
											<?php if ($articleId > 0): ?>
												<a href="/admin/content/blog/articles/<?= $articleId ?>/" class="text-decoration-none">
													<?= htmlspecialchars($articleTitle !== '' ? $articleTitle : ('Статья #' . $articleId)) ?>
												</a>
											<?php else: ?>
												<?= htmlspecialchars($articleTitle !== '' ? $articleTitle : 'Без названия') ?>
											<?php endif; ?>
										</td>
										<td class="text-end fw-semibold"><?= $viewsCount ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
};

$renderRubricRow = static function (string $period, ?object $rubric): void {
	$rubricId = (int) ($rubric->id ?? 0);
	$rubricTitle = trim((string) ($rubric->title ?? ''));
	$viewsCount = (int) ($rubric->views_count ?? 0);
	?>
	<tr>
		<td class="text-secondary"><?= htmlspecialchars($period) ?></td>
		<td>
			<?php if ($rubric === null || $viewsCount <= 0): ?>
				<span class="text-secondary">Нет данных</span>
			<?php elseif ($rubricId > 0): ?>
				<a href="/admin/content/blog/rubrics/<?= $rubricId ?>/" class="text-decoration-none">
					<?= htmlspecialchars($rubricTitle !== '' ? $rubricTitle : ('Рубрика #' . $rubricId)) ?>
				</a>
			<?php else: ?>
				<?= htmlspecialchars($rubricTitle !== '' ? $rubricTitle : 'Без названия') ?>
			<?php endif; ?>
		</td>
		<td class="text-end fw-semibold"><?= $rubric !== null && $viewsCount > 0 ? $viewsCount : '—' ?></td>
	</tr>
	<?php
};
?>

<section class="admin-dashboard">
	<style>
		.admin-dashboard__section {
			padding: 25px 0;
		}

		.admin-dashboard__subsection {
			margin-top: 1.5rem;
		}

		.admin-dashboard__subsection-title {
			font-size: 0.95rem;
			font-weight: 600;
			margin-bottom: 0.75rem;
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
		</div>

		<div class="admin-dashboard__subsection">
			<h4 class="admin-dashboard__subsection-title text-secondary">Информация о Блоге</h4>
			<div class="row g-3">
				<?php $renderStatCard('Рубрики', (string) $rubricsCount); ?>
				<?php $renderStatCard('Статьи', (string) $articlesCount); ?>
				<?php $renderStatCard('Просмотры за неделю', (string) $blogViewsWeekCount); ?>
				<?php $renderStatCard('Просмотры за месяц', (string) $blogViewsMonthCount); ?>
			</div>

			<div class="row g-3 mt-1">
				<?php $renderArticlesTopTable('Топ 5 статей за неделю', $topArticlesWeek); ?>
				<?php $renderArticlesTopTable('Топ 5 статей за месяц', $topArticlesMonth); ?>
				<?php $renderArticlesTopTable('Топ 5 статей за все время', $topArticlesAllTime); ?>
			</div>

			<div class="row g-3 mt-1">
				<div class="col-12">
					<div class="card border-0 shadow-sm">
						<div class="card-body">
							<h5 class="h6 mb-3">Самые просматриваемые рубрики</h5>
							<div class="table-responsive">
								<table class="table table-sm align-middle mb-0">
									<thead class="table-light">
										<tr>
											<th scope="col" style="width: 12rem;">Период</th>
											<th scope="col">Рубрика</th>
											<th scope="col" class="text-end" style="width: 5rem;">Просмотры</th>
										</tr>
									</thead>
									<tbody>
										<?php $renderRubricRow('За неделю', $topRubricWeek); ?>
										<?php $renderRubricRow('За месяц', $topRubricMonth); ?>
										<?php $renderRubricRow('За все время', $topRubricAllTime); ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
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
