<?php
$path = (string) $this->getParam('path');
$content = (string) $this->getParam('content');
$isEditMode = (bool) $this->getParam('edit_mode');
?>

<?php if ($isEditMode): ?>
	<div class="include-area include-area_editable" data-include-area-path="<?= htmlspecialchars($path) ?>" title="Двойной клик для редактирования">
		<?= $content ?>
	</div>
<?php else: ?>
	<?= $content ?>
<?php endif; ?>
