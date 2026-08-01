<?php if ($this->getParam('show')): ?>
	<?php
	$backUrl = rawurlencode((string) ($this->getParam('back_url') ?? '/'));
	$isEditMode = (bool) $this->getParam('is_edit_mode');
	$editToggleUrl = (string) $this->getParam('edit_toggle_url');
	?>
	<div class="admin-bar" role="toolbar" aria-label="Панель администратора">
		<div class="admin-bar__inner">
			<div class="admin-bar__side admin-bar__side--left">
				<a class="admin-bar__admin-button" href="/admin/">Админка</a>
			</div>

			<div class="admin-bar__center" aria-hidden="true"></div>

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

				<a class="admin-bar__button" href="/auth/logout/?back=<?= $backUrl ?>" title="Выйти" aria-label="Выйти">
					<svg class="admin-bar__icon" width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
						<path d="M10 7V6C10 4.89543 10.8954 4 12 4H18C19.1046 4 20 4.89543 20 6V18C20 19.1046 19.1046 20 18 20H12C10.8954 20 10 19.1046 10 18V17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
						<path d="M13 12H4M4 12L6.5 9.5M4 12L6.5 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?>
