<?php
$path = (string) $this->getParam('path');
$alt = (string) $this->getParam('alt');
$title = (string) $this->getParam('title');
$loading = (string) $this->getParam('loading');
?>

<img src="<?= htmlspecialchars($path) ?>"
     alt="<?= htmlspecialchars($alt) ?>"
     loading="<?= $loading ?>"
     decoding="async"
	<?= $loading === 'eager' ? 'fetchpriority="high"' : '' ?>
	<?= $title !== '' ? 'title="' . htmlspecialchars($title) . '"' : '' ?>>
