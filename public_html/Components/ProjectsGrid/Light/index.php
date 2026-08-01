<div class="projects-light-grid">
	<?php foreach ($this->getParam('items') ?? [] as $project): ?>
		<?php
		$projectId = (int) ($project->id ?? 0);
		$projectName = (string) ($project->name ?? '');
		$previewText = trim((string) ($project->preview_text ?? ''));
		$previewImage = !empty($project->preview_image_url)
			? (string) $project->preview_image_url
			: '/Components/ProjectsGrid/Default/img/no-image.webp';
		?>
		<a class="projects-light-card"
		   href="/portfolio/<?= $projectId ?>/"
		   aria-label="<?= htmlspecialchars($projectName) ?>">
			<div class="projects-light-card__media">
				<img class="projects-light-card__image"
				     src="<?= htmlspecialchars($previewImage) ?>"
				     alt="<?= htmlspecialchars($projectName) ?>">
			</div>

			<div class="projects-light-card__body">
				<h3 class="projects-light-card__title">
					<?= htmlspecialchars($projectName) ?>
				</h3>

				<?php if ($previewText !== ''): ?>
					<p class="projects-light-card__text"><?= htmlspecialchars($previewText) ?></p>
				<?php endif; ?>

				<?php if ($this->getParam('show_tags') && !empty($project->tags)): ?>
					<div class="projects-light-card__tags">
						<?php foreach ($project->tags as $tag): ?>
							<span class="projects-light-card__tag"><?= htmlspecialchars((string) ($tag->name ?? '')) ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</a>
	<?php endforeach; ?>
</div>
