<?php if ($this->getParam('show')): ?>
	<?php
	$backUrl = rawurlencode((string) ($this->getParam('back_url') ?? '/'));
	$isEditMode = (bool) $this->getParam('is_edit_mode');
	$editToggleUrl = (string) $this->getParam('edit_toggle_url');
	$groups = $this->getParam('groups');
	$groups = is_array($groups) ? $groups : [];
	?>
	<div class="admin-bar" role="toolbar" aria-label="Панель администратора">
		<div class="admin-bar__inner">
			<div class="admin-bar__side admin-bar__side--left">
				<a class="admin-bar__admin-button" href="/admin/">Админка</a>
			</div>

			<div class="admin-bar__center">
				<?php foreach ($groups as $group): ?>
					<?php if (!$group instanceof \App\Services\Admin\Bar\AdminBarGroup): ?>
						<?php continue; ?>
					<?php endif; ?>
					<div class="admin-bar__group" data-admin-bar-group="<?= htmlspecialchars($group->getId()) ?>">
						<span class="admin-bar__group-label"><?= htmlspecialchars($group->getLabel()) ?></span>
						<div class="admin-bar__group-actions">
							<?php foreach ($group->getActions() as $action): ?>
								<?php
								$attributes = $action->getAttributes();
								$attrHtml = '';
								foreach ($attributes as $attrName => $attrValue) {
									if ($attrName === 'type' && $action->getType() === 'button') {
										continue;
									}
									$attrHtml .= ' ' . htmlspecialchars((string) $attrName)
										. '="' . htmlspecialchars((string) $attrValue) . '"';
								}
								?>
								<?php if ($action->getType() === 'link'): ?>
									<a
										class="admin-bar__action"
										href="<?= htmlspecialchars($action->getHref()) ?>"
										<?= $attrHtml ?>
									><?= htmlspecialchars($action->getLabel()) ?></a>
								<?php else: ?>
									<button
										class="admin-bar__action"
										type="button"
										<?= $attrHtml ?>
									><?= htmlspecialchars($action->getLabel()) ?></button>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="admin-bar__side admin-bar__side--right">
				<a class="admin-bar__toggle<?= $isEditMode ? ' is-active' : '' ?>"
				   href="<?= htmlspecialchars($editToggleUrl) ?>"
				   title="<?= $isEditMode ? 'Выключить режим редактирования' : 'Включить режим редактирования' ?>"
				   aria-label="<?= $isEditMode ? 'Выключить режим редактирования' : 'Включить режим редактирования' ?>"
				   aria-pressed="<?= $isEditMode ? 'true' : 'false' ?>">
					<span class="admin-bar__toggle-track">
						<span class="admin-bar__toggle-thumb"></span>
					</span>
					<span class="admin-bar__toggle-label">Редактирование</span>
				</a>

				<a class="admin-bar__logout" href="/auth/logout/?back=<?= $backUrl ?>" title="Выйти" aria-label="Выйти">
					<span class="admin-bar__logout-label">Выход</span>
					<svg class="admin-bar__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
						<path d="M10 7V6C10 4.89543 10.8954 4 12 4H18C19.1046 4 20 4.89543 20 6V18C20 19.1046 19.1046 20 18 20H12C10.8954 20 10 19.1046 10 18V17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
						<path d="M13 12H4M4 12L6.5 9.5M4 12L6.5 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?>
