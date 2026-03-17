<?php
$projectsGrid = new \Components\ProjectsGrid\ProjectsGrid([
	'use_filters' => true,
]);
?>

<div class="container">
	<?php
	$projectsGrid->render();
	?>
</div>
