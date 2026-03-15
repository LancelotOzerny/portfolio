<h6 class="fw-bold mb-3">Меню</h6>
<ul class="list-unstyled">
	<?php foreach ($this->getParam('items') as $item): ?>
	<li>
		<a href="<?= $item['link'] ?>" class="text-light text-decoration-none"><?= $item['name'] ?></a>
	</li>
	<?php endforeach; ?>
</ul>