<?php
$portfolioConfig = \Modules\Main\Config::getInstance()->get('App', 'portfolio');
$projectsGridTemplate = trim((string) ($portfolioConfig->projects_grid_template ?? 'Default'));

$projectsGrid = new \Components\ProjectsGrid\ProjectsGrid([
	'use_filters' => true,
	'template' => $projectsGridTemplate !== '' ? $projectsGridTemplate : 'Default',
]);
?>

<div class="container">
	<?php
	$projectsGrid->render();
	?>
</div>
