<?php
$isFirstFilterElement = true;
?>

<!-- FILTERS -->
<?php if ($this->getParam('use_filters')): ?>
	<div id="projects-grid-filters" class="filter-buttons text-center mt-5">
		<?php foreach ($this->getParam('filters') as $key => $filter): ?>
			<button class="filter btn btn-outline-primary <?= $isFirstFilterElement ? 'active' : '' ?>" data-filter="filter-<?= $key ?>"><?= $filter ?></button>
			<?php
			$isFirstFilterElement = false;
		endforeach;
		?>
	</div>
<?php endif; ?>

<!-- PROJECTS -->
<div class="projects-grid projects-grid--hover row g-4 <?php if ($this->getParam('use_filters')) echo 'pt-5' ?>">
	<?php foreach ($this->getParam('items') ?? [] as $project): ?>
		<div class="project-item col-lg-4 col-md-6">
			<a class="project-card project-card--hover"
			   href="/portfolio/projects/<?= (int) ($project->id ?? 0) ?>/"
			   aria-label="<?= htmlspecialchars((string) ($project->name ?? '')) ?>">
				<div class="project-card__media">
					<img src="<?= !empty($project->preview_image_url) ? $project->preview_image_url : '/Components/ProjectsGrid/Default/img/no-image.webp' ?>"
					     class="project-card__image"
					     alt="">

					<div class="project-card__gradient" aria-hidden="true"></div>
					<div class="project-card__shade" aria-hidden="true"></div>

					<?php if (trim((string) ($project->preview_text ?? '')) !== ''): ?>
						<div class="project-card__preview-wrap">
							<p class="project-card__preview"><?= htmlspecialchars((string) ($project->preview_text ?? '')) ?></p>
						</div>
					<?php endif; ?>

					<div class="project-card__content">
						<h5 class="project-card__title"><?= htmlspecialchars((string) ($project->name ?? '')) ?></h5>

						<?php if ($project->tags): ?>
							<div class="project-card__tags">
								<?php foreach ($project->tags as $tag): ?>
									<span <?php if ($this->getParam('use_filters')) echo 'data-filter-target="filter-' . (int) ($tag->id ?? 0) . '"'; ?> class="project-card__tag"><?= htmlspecialchars((string) ($tag->name ?? '')) ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</a>
		</div>
	<?php endforeach; ?>
</div>
