<?php
return [
	[
		'path'     => '/admin/',
		'method'   => 'prefix',
		'template' => 'Admin',
	],
	[
		'path'     => '/portfolio/projects/',
		'method'   => 'prefix',
		'template' => 'Detail',
	],
	[
		'path'     => '/',
		'method'   => 'equal',
		'template' => 'Main',
	],
	[
		'path'     => '/',
		'method'   => 'prefix',
		'template' => 'Inner',
	],
];
