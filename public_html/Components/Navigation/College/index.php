<?php
$items = $this->getParam('items') ?? [];

$renderIcon = static function (string $icon): void {
	$paths = [
		'user' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M4 20a8 8 0 0 1 16 0"/>',
		'award' => '<path d="m8 13-2 8 6-3 6 3-2-8"/><circle cx="12" cy="8" r="5"/>',
		'grid' => '<path d="M4 4h6v6H4z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6H4z"/><path d="M14 14h6v6h-6z"/>',
		'code' => '<path d="m8 9-4 3 4 3"/><path d="m16 9 4 3-4 3"/><path d="m14 5-4 14"/>',
		'mail' => '<path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/>',
	];

	echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ($paths[$icon] ?? $paths['grid']) . '</svg>';
};
?>

<header class="college-navigation" id="site-header" data-college-navigation>
	<a class="college-navigation__brand" href="/" aria-label="Максим Беляков">
		<img class="college-navigation__avatar" src="/upload/images/main/profile.png" alt="" loading="eager" decoding="async" fetchpriority="high">
		<span class="college-navigation__name">Максим <b>Беляков</b></span>
	</a>

	<button class="college-navigation__toggle" type="button" aria-label="Открыть меню" aria-expanded="false" data-college-nav-toggle>
		<span></span>
		<span></span>
		<span></span>
	</button>

	<nav class="college-navigation__panel" aria-label="Главное меню">
		<ul class="college-navigation__list">
			<?php foreach ($items as $index => $item): ?>
				<?php
				$children = $item['children'] ?? [];
				$hasChildren = !empty($children);
				$dropdownId = 'college-navigation-menu-' . $index;
				?>
				<li class="college-navigation__item <?= $hasChildren ? 'college-navigation__item_dropdown' : '' ?>">
					<?php if ($hasChildren): ?>
						<button class="college-navigation__link <?= $item['active'] ? 'is-active' : '' ?>"
						        type="button"
						        aria-expanded="false"
						        aria-controls="<?= $dropdownId ?>"
						        data-college-dropdown-toggle>
							<?= htmlspecialchars((string) ($item['name'] ?? '')) ?>
							<span class="college-navigation__chevron" aria-hidden="true"></span>
						</button>

						<div class="college-navigation__dropdown" id="<?= $dropdownId ?>">
							<?php foreach ($children as $child): ?>
								<a class="college-navigation__dropdown-link" href="<?= htmlspecialchars((string) ($child['link'] ?? '#')) ?>">
									<span class="college-navigation__dropdown-icon">
										<?php $renderIcon((string) ($child['icon'] ?? 'grid')); ?>
									</span>
									<span class="college-navigation__dropdown-copy">
										<span class="college-navigation__dropdown-title"><?= htmlspecialchars((string) ($child['name'] ?? '')) ?></span>
										<span class="college-navigation__dropdown-text"><?= htmlspecialchars((string) ($child['description'] ?? '')) ?></span>
									</span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<a class="college-navigation__link <?= $item['active'] ? 'is-active' : '' ?>" href="<?= htmlspecialchars((string) ($item['link'] ?? '#')) ?>">
							<?= htmlspecialchars((string) ($item['name'] ?? '')) ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</header>
