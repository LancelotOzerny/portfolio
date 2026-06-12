<header class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
	<a class="navbar-brand" href="/">Lancy</a>

	<div class="collapse navbar-collapse" id="navbarNav">
		<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
			<?php foreach ($this->getParam('items') as $item): ?>
				<li class="nav-item">
					<a class="nav-link <?= $item['active'] ? 'active' : '' ?>" aria-current="page" href="<?= $item['link'] ?>"><?= $item['name'] ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</header>