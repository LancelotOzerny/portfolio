<?php
return [
	0 => [
		'path' => '/admin/',
		'method' => 'prefix',
		'template' => 'Admin',
	],
	1 => [
		'path' => '/portfolio/projects/',
		'method' => 'prefix',
		'template' => 'Detail',
	],
	2 => [
		'path' => '/',
		'method' => 'equal',
		'template' => 'MainLight',
	],
	3 => [
		'path' => '/',
		'method' => 'prefix',
		'template' => 'Inner',
	],
];
