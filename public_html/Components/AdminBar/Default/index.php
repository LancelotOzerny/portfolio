<?php if ($this->getParam('show')): ?>
	<?php $backUrl = rawurlencode((string) ($this->getParam('back_url') ?? '/')); ?>
	<div class="admin-bar" role="toolbar" aria-label="Панель администратора">
		<div class="admin-bar__inner">
			<a class="admin-bar__button" href="/admin/" title="Панель управления" aria-label="Панель управления">
				<svg class="admin-bar__icon" width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M12 15.2C13.7673 15.2 15.2 13.7673 15.2 12C15.2 10.2327 13.7673 8.8 12 8.8C10.2327 8.8 8.8 10.2327 8.8 12C8.8 13.7673 10.2327 15.2 12 15.2Z" stroke="currentColor" stroke-width="1.6"/>
					<path d="M4 12H5.2M18.8 12H20M12 4V5.2M12 18.8V20M6.34 6.34L7.19 7.19M16.81 16.81L17.66 17.66M6.34 17.66L7.19 16.81M16.81 7.19L17.66 6.34" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
					<path d="M3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12Z" stroke="currentColor" stroke-width="1.6"/>
				</svg>
			</a>
			<a class="admin-bar__button" href="/auth/logout/?back=<?= $backUrl ?>" title="Выйти" aria-label="Выйти">
				<svg class="admin-bar__icon" width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M10 7V6C10 4.89543 10.8954 4 12 4H18C19.1046 4 20 4.89543 20 6V18C20 19.1046 19.1046 20 18 20H12C10.8954 20 10 19.1046 10 18V17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
					<path d="M13 12H4M4 12L6.5 9.5M4 12L6.5 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</a>
		</div>
	</div>
<?php endif; ?>
