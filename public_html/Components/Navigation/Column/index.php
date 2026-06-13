<div class="nav-column">
	<div class="site-footer__title">&#1052;&#1077;&#1085;&#1102; &#1089;&#1072;&#1081;&#1090;&#1072;</div>
	<ul class="site-footer__list nav-column__list">
		<?php foreach ($this->getParam('items') as $item): ?>
			<li class="nav-column__item">
				<a href="<?= $item['link'] ?>"><?= $item['name'] ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
