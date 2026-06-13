<header class="navbar main-navigation" data-main-navigation>
	<a class="navbar-brand main-navigation__brand" href="/" aria-label="Lancy studio">Lancy</a>

	<button class="main-navigation__toggle" type="button" aria-label="Open menu" aria-expanded="false" data-nav-toggle>
		<span></span>
		<span></span>
		<span></span>
	</button>

	<nav class="main-navigation__panel" id="navbarNav" aria-label="Main menu">
		<ul class="navbar-nav main-navigation__list">
			<?php foreach ($this->getParam('items') as $item): ?>
				<li class="nav-item main-navigation__item">
					<a class="nav-link main-navigation__link <?= $item['active'] ? 'active' : '' ?>" aria-current="<?= $item['active'] ? 'page' : 'false' ?>" href="<?= $item['link'] ?>"><?= $item['name'] ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</header>
