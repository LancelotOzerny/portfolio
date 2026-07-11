<?php
return [
	'site' => [
		'name' => 'Lancy Studio',
		'domain' => 'https://lancy.dev',
		'title_template' => '%title% — %site_name%',
		'default_title' => 'Lancy Studio',
		'default_description' => 'Портфолио PHP и FullStack-разработчика. Создание сайтов и веб-приложений на PHP, MySQL, Bitrix, HTML и JavaScript.',
		'default_og_image' => '/upload/images/main/profile.png',
	],
	'pages' => [
		'home' => [
			'label' => 'Главная',
			'path' => '/',
			'title' => 'Портфолио PHP и FullStack разработчика',
			'description' => 'Максим Беляков — PHP/FullStack-разработчик из Липецка. Создаю сайты, backend и frontend на PHP, MySQL, Bitrix и JavaScript. Смотрите проекты и свяжитесь для сотрудничества.',
			'og_title' => 'Максим Беляков — разработка сайтов и веб-приложений',
			'og_description' => 'Портфолио FullStack-разработчика: реальные проекты, опыт работы и технологии PHP, MySQL, HTML, JavaScript.',
		],
		'about' => [
			'label' => 'О себе',
			'path' => '/about/',
			'title' => 'О разработчике Максиме Белякове',
			'description' => 'FullStack-разработчик с опытом более 2 лет. PHP, MySQL, 1C-Bitrix, HTML, CSS, JavaScript. Опыт работы, навыки, образование и подход к веб-разработке.',
			'og_title' => 'Максим Беляков — FullStack-разработчик',
			'og_description' => 'Кто я, чем занимаюсь и какие технологии использую: PHP, Bitrix, MySQL, frontend и развитие проектов под ключ.',
		],
		'portfolio' => [
			'label' => 'Портфолио',
			'path' => '/portfolio/',
			'title' => 'Портфолио веб-проектов',
			'description' => 'Примеры реализованных сайтов и веб-приложений с описанием технологий, задач и результатов. PHP, JavaScript, HTML, Bitrix и другие инструменты.',
			'og_title' => 'Портфолио проектов — Lancy Studio',
			'og_description' => 'Реализованные проекты разработчика: от идеи до готового продукта. Технологии, задачи и итоговый результат.',
		],
		'certificates' => [
			'label' => 'Сертификаты',
			'path' => '/certificates/',
			'title' => 'Сертификаты и дипломы разработчика',
			'description' => 'Сертификаты, курсы и дипломы Максима Белякова: подтверждение квалификации в веб-разработке и информационных технологиях.',
			'og_title' => 'Сертификаты и обучение',
			'og_description' => 'Подтверждённая квалификация: курсы, тренинги и диплом ВолГУ по направлению «Информационные системы и технологии».',
		],
		'contacts' => [
			'label' => 'Контакты',
			'path' => '/contacts/',
			'title' => 'Контакты для связи и сотрудничества',
			'description' => 'Свяжитесь с Максимом Беляковым для обсуждения проекта, консультации или сотрудничества. Липецк, телефон, email и форма обратной связи.',
			'og_title' => 'Связаться с разработчиком',
			'og_description' => 'Готов ответить на вопросы и обсудить ваш проект. Телефон, email и удобная форма обратной связи.',
		],
	],
];
