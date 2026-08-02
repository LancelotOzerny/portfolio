<?php
(new \Components\BlogSections\BlogSections([
	'template' => 'Admin',
	'only_enabled' => false,
	'flash' => $data['flash'] ?? null,
]))->render();
