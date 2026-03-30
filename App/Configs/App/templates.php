<?php
return [
	[
		'path'     => '/portfolio/projects/',
		'method'   => 'prefix',
		'template' => 'Detail',
	],
	[
		'path'     => '/',
		'method'   => 'equal',
		'template' => 'Default',
	],
	[
		'path'     => '/',
		'method'   => 'prefix',
		'template' => 'Inner',
	],
];