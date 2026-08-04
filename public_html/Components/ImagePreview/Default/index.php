<?php
$path = (string) $this->getParam('path');
$alt = (string) $this->getParam('alt');
$title = (string) $this->getParam('title');
?>

<img src="<?= htmlspecialchars($path) ?>" alt="<?= htmlspecialchars($alt) ?>"<?= $title !== '' ? ' title="' . htmlspecialchars($title) . '"' : '' ?>>
