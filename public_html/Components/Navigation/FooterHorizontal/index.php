<?php
$items = $this->getParam('items') ?? [];

foreach ($items as $item):
	$link = (string) ($item['link'] ?? '#');
	$name = (string) ($item['name'] ?? '');
	?>
	<a href="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars($name) ?></a>
<?php endforeach; ?>
