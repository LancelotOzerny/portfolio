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

			<div class="resume-light__list">
				<?php foreach ($experience as $index => $item): ?>
					<?php
					$itemId = (string) ((int) ($item->id ?? $index));
					$headerId = 'resume-light-header-' . $itemId;
					$descriptionId = 'resume-light-description-' . $itemId;
					?>
					<article class="resume-light__item<?= (int) ($item->is_current ?? 0) === 1 ? ' resume-light__item--current' : '' ?>">
						<?php if (!empty($item->duration_label)): ?>
							<span class="resume-light__duration"><?= preg_replace('/(?<=лет) |(?<=года) |(?<=год) /u', '<br>', htmlspecialchars((string) $item->duration_label)) ?></span>
						<?php endif; ?>
						<button class="resume-light__header" type="button" id="<?= $headerId ?>" aria-expanded="false" aria-controls="<?= $descriptionId ?>">
							<span class="resume-light__info">
								<span class="resume-light__position"><?= htmlspecialchars((string) ($item->position ?? '')) ?></span>
								<span class="resume-light__company"><?= htmlspecialchars((string) ($item->company ?? '')) ?></span>
							</span>
							<span class="resume-light__period"><?= htmlspecialchars((string) ($item->date_label ?? '')) ?></span>
							<span class="resume-light__arrow" aria-hidden="true"></span>
						</button>

						<div class="resume-light__description" id="<?= $descriptionId ?>" role="region" aria-labelledby="<?= $headerId ?>">
							<div class="resume-light__description-inner">
								<div class="resume-light__description-content"><?= (string) ($item->description ?? '') ?></div>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
