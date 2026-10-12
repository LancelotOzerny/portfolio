<?php
$projects = array_slice($this->getParam('items') ?? [], 0, 7);
$featuredProject = array_shift($projects);
$fallbackImage = '/Components/ProjectsGrid/Default/img/no-image.webp';
?>

<?php if ($featuredProject !== null): ?>
	<?php
	$featuredId = (int) ($featuredProject->id ?? 0);
	$featuredName = trim((string) ($featuredProject->name ?? 'Без названия'));
	$featuredText = trim((string) ($featuredProject->preview_text ?? ''));
	$featuredImage = trim((string) ($featuredProject->preview_image_url ?? '')) ?: $fallbackImage;
	$featuredTags = $this->getParam('show_tags') ? ($featuredProject->tags ?? []) : [];
	?>
	<div class="projects-main">
		<article class="projects-main__featured">
			<div class="projects-main__featured-content">
				<?php if (!empty($featuredTags)): ?>
					<span class="projects-main__label"><?= htmlspecialchars((string) ($featuredTags[0]->name ?? 'Проект')) ?></span>
				<?php endif; ?>

				<h3 class="projects-main__featured-title"><?= htmlspecialchars($featuredName) ?></h3>

				<?php if ($featuredText !== ''): ?>
					<p class="projects-main__featured-text"><?= htmlspecialchars($featuredText) ?></p>
				<?php endif; ?>

				<a class="projects-main__featured-link" href="/portfolio/<?= $featuredId ?>/">
					Подробнее <span aria-hidden="true">→</span>
				</a>

				<?php if (!empty($featuredTags)): ?>
					<div class="projects-main__tags" aria-label="Технологии и направления">
						<?php foreach ($featuredTags as $tag): ?>
							<span class="projects-main__tag"><?= htmlspecialchars((string) ($tag->name ?? '')) ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<a class="projects-main__featured-media" href="/portfolio/<?= $featuredId ?>/" aria-label="Открыть проект: <?= htmlspecialchars($featuredName) ?>">
				<img src="<?= htmlspecialchars($featuredImage) ?>" alt="<?= htmlspecialchars($featuredName) ?>" loading="lazy" decoding="async">
			</a>
		</article>

		<?php if (!empty($projects)): ?>
			<div class="projects-main__grid">
				<?php foreach ($projects as $project): ?>
					<?php
					$projectId = (int) ($project->id ?? 0);
					$projectName = trim((string) ($project->name ?? 'Без названия'));
					$projectText = trim((string) ($project->preview_text ?? ''));
					$projectImage = trim((string) ($project->preview_image_url ?? '')) ?: $fallbackImage;
					$projectTags = $this->getParam('show_tags') ? ($project->tags ?? []) : [];
					?>
					<article class="projects-main__card">
						<a class="projects-main__card-media" href="/portfolio/<?= $projectId ?>/" aria-label="Открыть проект: <?= htmlspecialchars($projectName) ?>">
							<img src="<?= htmlspecialchars($projectImage) ?>" alt="<?= htmlspecialchars($projectName) ?>" loading="lazy" decoding="async">
						</a>

						<div class="projects-main__card-content">
							<div class="projects-main__card-top">
								<?php if (!empty($projectTags)): ?>
									<span class="projects-main__label"><?= htmlspecialchars((string) ($projectTags[0]->name ?? 'Проект')) ?></span>
								<?php endif; ?>
								<a class="projects-main__card-arrow" href="/portfolio/<?= $projectId ?>/" aria-label="Открыть проект: <?= htmlspecialchars($projectName) ?>">↗</a>
							</div>

							<h3 class="projects-main__card-title"><a href="/portfolio/<?= $projectId ?>/"><?= htmlspecialchars($projectName) ?></a></h3>

							<?php if ($projectText !== ''): ?>
								<p class="projects-main__card-text"><?= htmlspecialchars($projectText) ?></p>
							<?php endif; ?>

							<?php if (!empty($projectTags)): ?>
								<div class="projects-main__tags" aria-label="Технологии и направления">
									<?php foreach ($projectTags as $tag): ?>
										<span class="projects-main__tag"><?= htmlspecialchars((string) ($tag->name ?? '')) ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
