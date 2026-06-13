<div class="nav-column">
	<div class="site-footer__title">Меню сайта</div>
	<ul class="site-footer__list nav-column__list">
		<?php foreach ($this->getParam('items') as $item): ?>
			<li class="nav-column__item">
				<a href="<?= $item['link'] ?>"><?= $item['name'] ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
