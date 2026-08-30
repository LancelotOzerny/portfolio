<?php
/* @var array $data */

$topRubricsAllTime = is_array($data['topRubricsAllTime'] ?? null) ? $data['topRubricsAllTime'] : [];
$topRubricsMonth = is_array($data['topRubricsMonth'] ?? null) ? $data['topRubricsMonth'] : [];
$topArticlesWeek = is_array($data['topArticlesWeek'] ?? null) ? $data['topArticlesWeek'] : [];
$topArticlesMonth = is_array($data['topArticlesMonth'] ?? null) ? $data['topArticlesMonth'] : [];
$topArticlesAllTime = is_array($data['topArticlesAllTime'] ?? null) ? $data['topArticlesAllTime'] : [];
$topCommentedWeek = is_array($data['topCommentedWeek'] ?? null) ? $data['topCommentedWeek'] : [];
$topCommentedMonth = is_array($data['topCommentedMonth'] ?? null) ? $data['topCommentedMonth'] : [];
$topCommentedYear = is_array($data['topCommentedYear'] ?? null) ? $data['topCommentedYear'] : [];
$topRatedArticles = is_array($data['topRatedArticles'] ?? null) ? $data['topRatedArticles'] : [];
$defaultImage = '/Templates/Inner/img/no-image.webp';

$renderSectionTitle = static function (string $title): void {
	?>
	<h3 class="admin-dashboard__section-title">
		<span class="admin-dashboard__section-title-line" aria-hidden="true"></span>
		<span class="admin-dashboard__section-title-text"><?= htmlspecialchars($title) ?></span>
		<span class="admin-dashboard__section-title-line" aria-hidden="true"></span>
	</h3>
	<?php
};

$articleLabel = static function (object $item): string {
	$articleId = (int) ($item->id ?? 0);
	$title = trim((string) ($item->title ?? ''));

	return $title !== '' ? $title : ($articleId > 0 ? 'Статья #' . $articleId : 'Без названия');
};

$renderRankedList = static function (
	array $items,
	string $countField,
	string $emptyText,
	string $hrefPrefix
): void {
	if ($items === []) {
		echo '<p class="text-secondary small mb-0">' . htmlspecialchars($emptyText) . '</p>';
		return;
	}

	echo '<ol class="admin-statistics__rank-list">';
	foreach ($items as $index => $item) {
		if (!is_object($item)) {
			continue;
		}

		$itemId = (int) ($item->id ?? 0);
		$title = trim((string) ($item->title ?? ''));
		$count = (int) ($item->{$countField} ?? 0);
		$label = $title !== '' ? $title : ($itemId > 0 ? '#' . $itemId : 'Без названия');
		?>
		<li class="admin-statistics__rank-item">
			<span class="admin-statistics__rank-num"><?= $index + 1 ?></span>
			<span class="admin-statistics__rank-body">
				<?php if ($itemId > 0): ?>
					<a href="<?= htmlspecialchars($hrefPrefix . $itemId . '/') ?>" class="admin-statistics__rank-link">
						<?= htmlspecialchars($label) ?>
					</a>
				<?php else: ?>
					<span><?= htmlspecialchars($label) ?></span>
				<?php endif; ?>
			</span>
			<span class="admin-statistics__rank-count"><?= $count ?></span>
		</li>
		<?php
	}
	echo '</ol>';
};

$renderBarChart = static function (string $periodTitle, array $items, callable $articleLabel): void {
	$maxValue = 0;
	foreach ($items as $item) {
		if (!is_object($item)) {
			continue;
		}
		$maxValue = max($maxValue, (int) ($item->views_count ?? 0));
	}
	?>
	<div class="admin-statistics__chart">
		<h4 class="admin-statistics__subtitle"><?= htmlspecialchars($periodTitle) ?></h4>
		<?php if ($items === [] || $maxValue <= 0): ?>
			<p class="text-secondary small mb-0">Нет данных.</p>
		<?php else: ?>
			<div class="admin-statistics__plot" role="img" aria-label="<?= htmlspecialchars($periodTitle) ?>">
				<div class="admin-statistics__bars">
					<?php foreach ($items as $item): ?>
						<?php
						if (!is_object($item)) {
							continue;
						}
						$viewsCount = (int) ($item->views_count ?? 0);
						$heightPercent = $maxValue > 0 ? max(6, (int) round(($viewsCount / $maxValue) * 100)) : 0;
						$label = $articleLabel($item);
						?>
						<div class="admin-statistics__bar-col" title="<?= htmlspecialchars($label, ENT_QUOTES) ?>">
							<span class="admin-statistics__bar-value"><?= $viewsCount ?></span>
							<div class="admin-statistics__bar-track">
								<div class="admin-statistics__bar" style="height: <?= $heightPercent ?>%;"></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="admin-statistics__bar-captions">
					<?php foreach ($items as $index => $item): ?>
						<?php
						if (!is_object($item)) {
							continue;
						}
						$articleId = (int) ($item->id ?? 0);
						$label = $articleLabel($item);
						?>
						<?php if ($articleId > 0): ?>
							<a
								class="admin-statistics__bar-label"
								href="/admin/content/blog/articles/<?= $articleId ?>/"
								title="<?= htmlspecialchars($label, ENT_QUOTES) ?>"
							><?= $index + 1 ?>. <?= htmlspecialchars($label) ?></a>
						<?php else: ?>
							<span class="admin-statistics__bar-label" title="<?= htmlspecialchars($label, ENT_QUOTES) ?>">
								<?= $index + 1 ?>. <?= htmlspecialchars($label) ?>
							</span>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
};

$renderStars = static function (float $rating): void {
	$fullStars = (int) floor($rating);
	$hasHalf = ($rating - $fullStars) >= 0.5;
	echo '<span class="admin-statistics__stars" aria-label="Рейтинг ' . htmlspecialchars((string) $rating) . '">';
	for ($i = 1; $i <= 5; $i++) {
		$class = 'admin-statistics__star';
		if ($i <= $fullStars) {
			$class .= ' is-full';
		} elseif ($i === $fullStars + 1 && $hasHalf) {
			$class .= ' is-half';
		}
		echo '<span class="' . $class . '" aria-hidden="true"></span>';
	}
	echo '</span>';
};
?>

<section class="admin-statistics">
	<style>
		.admin-statistics__section + .admin-statistics__section {
			margin-top: 100px;
		}

		.admin-statistics .admin-dashboard__section-title {
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
			padding: 0 0 50px 0;
			margin: 0;
		}

		.admin-statistics .admin-dashboard__section-title-line {
			flex: 1 1 0;
			max-width: 120px;
			height: 1px;
			background: linear-gradient(to right, transparent, #ced4da 20%, #ced4da 80%, transparent);
		}

		.admin-statistics .admin-dashboard__section-title-text {
			flex: 0 0 auto;
			padding: 0.35rem 1rem;
			border-top: 1px solid #dee2e6;
			border-bottom: 1px solid #dee2e6;
		}

		.admin-statistics__subtitle {
			font-size: 0.875rem;
			font-weight: 600;
			line-height: 1.3;
			text-transform: uppercase;
			letter-spacing: 0.06em;
			color: #6c757d;
			margin: 0 0 1rem;
		}

		.admin-statistics__columns {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 1.5rem;
		}

		.admin-statistics__columns--two {
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 100px;
		}

		.admin-statistics__charts {
			display: flex;
			flex-direction: column;
			gap: 50px;
		}

		.admin-statistics__rank-list {
			list-style: none;
			margin: 0;
			padding: 0;
		}

		.admin-statistics__rank-item {
			display: flex;
			align-items: center;
			gap: 0.75rem;
			padding: 0.65rem 0;
			border-bottom: 1px solid #e9ecef;
		}

		.admin-statistics__rank-item:last-child {
			border-bottom: 0;
		}

		.admin-statistics__rank-num {
			flex: 0 0 1.75rem;
			width: 1.75rem;
			height: 1.75rem;
			border-radius: 50%;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-size: 0.75rem;
			font-weight: 700;
			color: #495057;
			border: 1px solid #dee2e6;
		}

		.admin-statistics__rank-body {
			flex: 1 1 auto;
			min-width: 0;
		}

		.admin-statistics__rank-link {
			color: #212529;
			text-decoration: none;
		}

		.admin-statistics__rank-link:hover {
			text-decoration: underline;
		}

		.admin-statistics__rank-count {
			flex: 0 0 auto;
			font-weight: 600;
			color: #212529;
		}

		.admin-statistics__chart {
			min-width: 0;
			width: 100%;
			padding-bottom: 16px;
			border-bottom: 1px solid #ddd;
		}

		.admin-statistics__plot {
			width: 100%;
		}

		.admin-statistics__bars {
			display: flex;
			flex-wrap: nowrap;
			align-items: stretch;
			gap: 0.5rem;
			height: 200px;
		}

		.admin-statistics__bar-col {
			flex: 1 1 0;
			display: flex;
			flex-direction: column;
			align-items: center;
			min-width: 0;
			height: 100%;
		}

		.admin-statistics__bar-value {
			font-size: 0.75rem;
			font-weight: 600;
			color: #495057;
			margin-bottom: 0.35rem;
		}

		.admin-statistics__bar-track {
			flex: 1 1 auto;
			width: 100%;
			display: flex;
			align-items: flex-end;
			justify-content: center;
		}

		.admin-statistics__bar {
			width: 100%;
			max-width: 36px;
			border-radius: 6px 6px 0 0;
			background: linear-gradient(180deg, #0d6efd 0%, #6ea8fe 100%);
			min-height: 8px;
		}

		.admin-statistics__bar-captions {
			display: flex;
			flex-wrap: nowrap;
			gap: 0.5rem;
			margin-top: 0.5rem;
		}

		.admin-statistics__bar-label {
			flex: 1 1 0;
			min-width: 0;
			display: -webkit-box;
			-webkit-box-orient: vertical;
			-webkit-line-clamp: 2;
			overflow: hidden;
			font-size: 0.68rem;
			line-height: 1.2;
			text-align: center;
			color: #495057;
			text-decoration: none;
		}

		.admin-statistics__bar-label:hover {
			color: #0d6efd;
		}

		.admin-statistics__tiles {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 1rem;
		}

		.admin-statistics__tile {
			display: flex;
			align-items: stretch;
			min-width: 0;
			text-decoration: none;
			color: inherit;
			border: 1px solid #e9ecef;
			border-radius: 0.5rem;
			overflow: hidden;
			background: transparent;
		}

		.admin-statistics__tile:hover {
			border-color: #ced4da;
		}

		.admin-statistics__tile-image {
			flex: 0 0 96px;
			width: 96px;
			object-fit: cover;
			height: 96px;
			background: #f8f9fa;
		}

		.admin-statistics__tile-body {
			display: flex;
			flex-direction: column;
			justify-content: center;
			gap: 0.35rem;
			padding: 0.75rem 0.85rem;
			min-width: 0;
		}

		.admin-statistics__tile-title {
			font-size: 0.9rem;
			font-weight: 600;
			line-height: 1.3;
			display: -webkit-box;
			-webkit-box-orient: vertical;
			-webkit-line-clamp: 2;
			overflow: hidden;
		}

		.admin-statistics__tile-meta {
			display: flex;
			align-items: center;
			gap: 0.5rem;
			font-size: 0.8rem;
			color: #6c757d;
		}

		.admin-statistics__stars {
			display: inline-flex;
			gap: 0.1rem;
		}

		.admin-statistics__star {
			width: 12px;
			height: 12px;
			background: #dee2e6;
			clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
		}

		.admin-statistics__star.is-full {
			background: #ffc107;
		}

		.admin-statistics__star.is-half {
			background: linear-gradient(90deg, #ffc107 50%, #dee2e6 50%);
		}

		@media (max-width: 991.98px) {
			.admin-statistics__columns,
			.admin-statistics__columns--two,
			.admin-statistics__tiles {
				grid-template-columns: 1fr;
			}
		}
	</style>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body p-4">
			<h2 class="h4 mb-2">Статистика блога</h2>
			<p class="text-secondary mb-0">Детальная информация по статьям и рубрикам.</p>
		</div>
	</div>

	<div class="admin-statistics__section">
		<?php $renderSectionTitle('Рейтинг рубрик'); ?>
		<div class="admin-statistics__columns admin-statistics__columns--two">
			<div>
				<h4 class="admin-statistics__subtitle">Топ 3 за все время</h4>
				<?php $renderRankedList($topRubricsAllTime, 'views_count', 'Нет данных.', '/admin/content/blog/rubrics/'); ?>
			</div>
			<div>
				<h4 class="admin-statistics__subtitle">Топ 3 за месяц</h4>
				<?php $renderRankedList($topRubricsMonth, 'views_count', 'Нет данных.', '/admin/content/blog/rubrics/'); ?>
			</div>
		</div>
	</div>

	<div class="admin-statistics__section">
		<?php $renderSectionTitle('Смотрят чаще всего'); ?>
		<div class="admin-statistics__charts">
			<?php $renderBarChart('За неделю', $topArticlesWeek, $articleLabel); ?>
			<?php $renderBarChart('За месяц', $topArticlesMonth, $articleLabel); ?>
			<?php $renderBarChart('За все время', $topArticlesAllTime, $articleLabel); ?>
		</div>
	</div>

	<div class="admin-statistics__section">
		<?php $renderSectionTitle('Больше всего комментариев'); ?>
		<div class="admin-statistics__columns">
			<div>
				<h4 class="admin-statistics__subtitle">За неделю</h4>
				<?php $renderRankedList($topCommentedWeek, 'comments_count', 'Нет данных.', '/admin/content/blog/articles/'); ?>
			</div>
			<div>
				<h4 class="admin-statistics__subtitle">За месяц</h4>
				<?php $renderRankedList($topCommentedMonth, 'comments_count', 'Нет данных.', '/admin/content/blog/articles/'); ?>
			</div>
			<div>
				<h4 class="admin-statistics__subtitle">За год</h4>
				<?php $renderRankedList($topCommentedYear, 'comments_count', 'Нет данных.', '/admin/content/blog/articles/'); ?>
			</div>
		</div>
	</div>

	<div class="admin-statistics__section">
		<?php $renderSectionTitle('Наивысший рейтинг'); ?>
		<?php if ($topRatedArticles === []): ?>
			<p class="text-secondary small mb-0">Нет данных.</p>
		<?php else: ?>
			<div class="admin-statistics__tiles">
				<?php foreach ($topRatedArticles as $article): ?>
					<?php
					if (!is_object($article)) {
						continue;
					}
					$articleId = (int) ($article->id ?? 0);
					$label = $articleLabel($article);
					$preview = trim((string) ($article->preview_image_path ?? ''));
					$avgRating = round((float) ($article->avg_rating ?? 0), 1);
					$votesCount = (int) ($article->votes_count ?? 0);
					$href = $articleId > 0 ? '/admin/content/blog/articles/' . $articleId . '/' : '#';
					?>
					<a class="admin-statistics__tile" href="<?= htmlspecialchars($href) ?>" title="<?= htmlspecialchars($label, ENT_QUOTES) ?>">
						<img
							class="admin-statistics__tile-image"
							src="<?= htmlspecialchars($preview !== '' ? $preview : $defaultImage) ?>"
							alt=""
							loading="lazy"
							decoding="async"
						>
						<span class="admin-statistics__tile-body">
							<span class="admin-statistics__tile-title"><?= htmlspecialchars($label) ?></span>
							<span class="admin-statistics__tile-meta">
								<?php $renderStars($avgRating); ?>
								<span><?= htmlspecialchars(number_format($avgRating, 1, ',', ' ')) ?></span>
								<span>· <?= $votesCount ?> <?= $votesCount % 10 === 1 && $votesCount % 100 !== 11 ? 'голос' : (($votesCount % 10 >= 2 && $votesCount % 10 <= 4 && ($votesCount % 100 < 12 || $votesCount % 100 > 14)) ? 'голоса' : 'голосов') ?></span>
							</span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
