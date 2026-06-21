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
			<?php foreach ($experience as $item): ?>
				<article class="card">
					<div class="card-body">
						<h4 class="card-title h5"><?= htmlspecialchars((string) ($item->position ?? '')) ?>, (<?= htmlspecialchars((string) ($item->company ?? '')) ?>)</h4>
						<div class="card-subtitle mb-2 text-muted"><?= htmlspecialchars((string) ($item->date_label ?? '')) ?></div>
						<?php if (trim((string) ($item->description ?? '')) !== ''): ?>
							<div class="card-text mt-3 resume-experience__description"><?= (string) $item->description ?></div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
