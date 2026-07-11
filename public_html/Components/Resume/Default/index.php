<?php
$showExperience = (bool) $this->getParam('show_experience');
$experience = $this->getParam('experience') ?? [];
$duration = trim((string) $this->getParam('experience_duration'));
?>

<?php if ($showExperience && !empty($experience)): ?>
	<section class="resume-experience scroll-show-area mb-5">
		<h3 class="border-bottom pb-2 mb-4">
			Опыт работы<?= $duration !== '' ? ' (' . htmlspecialchars($duration) . ')' : '' ?>
		</h3>

		<div class="d-grid gap-3">
			<?php foreach ($experience as $index => $item): ?>
				<?php
				$hasDescription = trim((string) ($item->description ?? '')) !== '';
				$collapseId = 'resume-experience-' . (int) ($item->id ?? $index);
				?>
				<article class="card resume-experience__item">
					<div class="card-body">
						<div class="resume-experience__header d-flex justify-content-between align-items-start gap-3">
							<div class="resume-experience__summary">
								<h4 class="card-title h5 mb-1"><?= htmlspecialchars((string) ($item->position ?? '')) ?></h4>
								<div class="card-subtitle text-muted mb-1"><?= htmlspecialchars((string) ($item->company ?? '')) ?></div>
								<div class="text-muted small"><?= htmlspecialchars((string) ($item->date_label ?? '')) ?></div>
							</div>
							<?php if ($hasDescription): ?>
								<button
									type="button"
									class="resume-experience__toggle flex-shrink-0"
									data-bs-toggle="collapse"
									data-bs-target="#<?= $collapseId ?>"
									aria-expanded="false"
									aria-controls="<?= $collapseId ?>"
									aria-label="Показать описание"
								>
									<svg class="resume-experience__chevron" width="22" height="22" viewBox="0 0 20 20" fill="none" aria-hidden="true">
										<path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</button>
							<?php endif; ?>
						</div>
						<?php if ($hasDescription): ?>
							<div class="collapse" id="<?= $collapseId ?>">
								<div class="card-text mt-3 resume-experience__description"><?= (string) $item->description ?></div>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
