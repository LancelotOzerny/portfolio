<header class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
	<div class="container">
		<a class="navbar-brand" href="/">LANCY</a>
		<button class="navbar-toggler" type="button"
				data-bs-toggle="collapse" data-bs-target="#navbarNav"
				aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php foreach ($this->getParam('items') as $item): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $item['active'] ? 'active' : '' ?>" aria-current="page" href="<?= $item['link'] ?>"><?= $item['name'] ?></a>
                </li>
                <?php endforeach; ?>
			</ul>
		</div>
	</div>
</header>