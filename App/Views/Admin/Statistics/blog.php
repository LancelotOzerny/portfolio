<?php
/* @var array $data */

$topArticlesWeek = is_array($data['topArticlesWeek'] ?? null) ? $data['topArticlesWeek'] : [];
$topArticlesMonth = is_array($data['topArticlesMonth'] ?? null) ? $data['topArticlesMonth'] : [];
$topArticlesAllTime = is_array($data['topArticlesAllTime'] ?? null) ? $data['topArticlesAllTime'] : [];
$topRubricWeek = is_object($data['topRubricWeek'] ?? null) ? $data['topRubricWeek'] : null;
$topRubricMonth = is_object($data['topRubricMonth'] ?? null) ? $data['topRubricMonth'] : null;
$topRubricAllTime = is_object($data['topRubricAllTime'] ?? null) ? $data['topRubricAllTime'] : null;

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

<section class="admin-statistics">
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body p-4">
			<h2 class="h4 mb-2">Статистика блога</h2>
			<p class="text-secondary mb-0">Детальная информация по статьям и рубрикам.</p>
		</div>
	</div>

	<div class="row g-3">
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
</section>
