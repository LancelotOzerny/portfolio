<?php
/* @var array $data */

$currentProject = $data['info'] ?? null;
if ($currentProject === null) {
	?>
	<section class="light-page-hero">
		<div class="site-container light-page-hero__container">
			<h1 class="page-title">Проект не найден</h1>
			<p class="light-page-hero__text">Возможно, проект был удален или ссылка изменилась.</p>
		</div>
	</section>
	<?php
	return;
}

$projectsGrid = new \Components\ProjectsGrid\ProjectsGrid([
	'use_filters' => false,
	'show_tags' => false,
	'limit' => 3,
	'random' => true,
	'exclude_id' => (int) $currentProject->id,
	'template' => 'Light',
]);

$projectInfoItems = array_values(array_filter(
	is_array($currentProject->info ?? null) ? $currentProject->info : [],
	static function ($item): bool {
		if (!is_object($item)) {
			return false;
		}

		return trim((string) ($item->date ?? '')) !== ''
			|| trim((string) ($item->develop_time ?? '')) !== ''
			|| trim((string) ($item->version ?? '')) !== '';
	}
));

$formatDate = static function (string $rawDate): string {
	$rawDate = trim($rawDate);
	if ($rawDate === '') {
		return '';
	}

	try {
		return (new DateTime($rawDate))->format('d/m/Y');
	} catch (Exception) {
		return $rawDate;
	}
};
?>

<section class="light-page-hero project-detail-hero">
	<div class="site-container light-page-hero__container">
		<a class="project-detail__back" href="/projects/">Назад к проектам</a>
		<h1 class="page-title"><?= htmlspecialchars((string) $currentProject->name) ?></h1>

		<?php if (trim((string) $currentProject->preview_text) !== ''): ?>
			<div class="light-page-hero__text">
				<?= $currentProject->preview_text ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="light-page-section project-detail">
	<div class="site-container project-detail__layout">
		<article class="project-detail__content">
			<?php if (!empty($currentProject->detail_image_url)): ?>
				<figure class="project-detail__image">
					<img src="<?= htmlspecialchars((string) $currentProject->detail_image_url) ?>"
					     alt="<?= htmlspecialchars((string) $currentProject->name) ?>"
					     title="<?= htmlspecialchars((string) $currentProject->name) ?>"
					     loading="lazy"
					     decoding="async">
				</figure>
			<?php endif; ?>

			<div class="project-detail__text">
				<?= $currentProject->detail_text ?>
			</div>
		</article>

		<aside class="project-detail__sidebar">
			<div class="project-detail__panel">
				<h2>Детали проекта</h2>

				<?php if (!empty($projectInfoItems)): ?>
					<?php foreach ($projectInfoItems as $infoIndex => $infoItem): ?>
						<?php
						$infoDate = $formatDate((string) ($infoItem->date ?? ''));
						$infoDevelopTime = trim((string) ($infoItem->develop_time ?? ''));
						$infoVersion = trim((string) ($infoItem->version ?? ''));
						?>

						<dl class="project-detail__meta">
							<?php if ($infoDate !== ''): ?>
								<div>
									<dt>Дата</dt>
									<dd><?= htmlspecialchars($infoDate) ?></dd>
								</div>
							<?php endif; ?>

							<?php if ($infoDevelopTime !== ''): ?>
								<div>
									<dt>Время разработки</dt>
									<dd><?= htmlspecialchars($infoDevelopTime) ?></dd>
								</div>
							<?php endif; ?>

							<?php if ($infoVersion !== ''): ?>
								<div>
									<dt>Версия</dt>
									<dd><?= htmlspecialchars($infoVersion) ?></dd>
								</div>
							<?php endif; ?>
						</dl>

						<?php if ($infoIndex < count($projectInfoItems) - 1): ?>
							<hr class="project-detail__divider">
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else: ?>
					<dl class="project-detail__meta">
						<div>
							<dt>Дата</dt>
							<dd><?= htmlspecialchars($formatDate((string) ($currentProject->created_at ?? ''))) ?></dd>
						</div>
					</dl>
				<?php endif; ?>

				<?php if (!empty($currentProject->links)): ?>
					<h2 class="project-detail__panel-title_links">Ссылки</h2>
					<div class="project-detail__links">
						<?php foreach ($currentProject->links as $link): ?>
							<a href="<?= htmlspecialchars((string) ($link->link ?? '')) ?>" target="_blank" rel="noopener">
								<?= htmlspecialchars((string) ($link->name ?? '')) ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</aside>
	</div>
</section>

<section class="light-page-section project-detail-related">
	<div class="site-container">
		<h2 class="page-title">Другие проекты</h2>
		<?php $projectsGrid->render(); ?>
	</div>
</section>
