<?php
$showExperience = (bool) $this->getParam('show_experience');
$experience = $this->getParam('experience') ?? [];
$duration = trim((string) $this->getParam('experience_duration'));
?>

<?php if ($showExperience && !empty($experience)): ?>
	<section class="resume-light" aria-labelledby="resumeLightTitle" data-resume-light>
		<div class="site-container resume-light__container">
			<h2 class="page-title" id="resumeLightTitle">
				Опыт работы<?= $duration !== '' ? ' (' . htmlspecialchars($duration) . ')' : '' ?>
			</h2>

			<div class="resume-light__tabs" role="tablist" aria-label="Опыт работы">
				<?php foreach ($experience as $index => $item): ?>
					<?php
					$itemId = (string) ((int) ($item->id ?? $index));
					$hasDescription = trim((string) ($item->description ?? '')) !== '';
					$buttonId = 'resume-light-tab-' . $itemId;
					$panelId = 'resume-light-panel-' . $itemId;
					?>
					<button
						class="resume-light-tab"
						type="button"
						id="<?= $buttonId ?>"
						role="tab"
						aria-selected="false"
						aria-controls="<?= $panelId ?>"
						<?= $hasDescription ? 'data-resume-tab="' . $itemId . '"' : 'disabled' ?>
					>
						<span class="resume-light-tab__position"><?= htmlspecialchars((string) ($item->position ?? '')) ?></span>
						<span class="resume-light-tab__company"><?= htmlspecialchars((string) ($item->company ?? '')) ?></span>
						<span class="resume-light-tab__period"><?= htmlspecialchars((string) ($item->date_label ?? '')) ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="resume-light__panels">
				<?php foreach ($experience as $index => $item): ?>
					<?php
					$itemId = (string) ((int) ($item->id ?? $index));
					$hasDescription = trim((string) ($item->description ?? '')) !== '';
					$buttonId = 'resume-light-tab-' . $itemId;
					$panelId = 'resume-light-panel-' . $itemId;
					?>
					<?php if ($hasDescription): ?>
						<article class="resume-light-panel" id="<?= $panelId ?>" role="tabpanel" aria-labelledby="<?= $buttonId ?>" data-resume-panel="<?= $itemId ?>" hidden>
							<div class="resume-light-panel__header">
								<h3 class="resume-light-panel__position"><?= htmlspecialchars((string) ($item->position ?? '')) ?></h3>
								<div class="resume-light-panel__meta">
									<span><?= htmlspecialchars((string) ($item->company ?? '')) ?></span>
									<span><?= htmlspecialchars((string) ($item->date_label ?? '')) ?></span>
								</div>
							</div>

							<div class="resume-light-panel__description"><?= (string) $item->description ?></div>
						</article>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
