<?php

namespace App\Listeners\Admin;

use App\Events\Admin\AdminMenuBuildEvent;
use App\Services\Admin\Menu\AdminMenuItem;

/**
 * Базовое меню админки. Приоритет высокий — выполняется первым.
 */
final class DefaultAdminMenuListener
{
	public function __invoke(AdminMenuBuildEvent $event): void
	{
		$event->addItem(new AdminMenuItem(
			id: 'home',
			label: 'Главная',
			href: '/admin/',
			icon: 'Гл',
			matchExact: ['/admin/'],
		));

		$event->addItem(new AdminMenuItem(
			id: 'statistics',
			label: 'Статистика',
			href: '/admin/statistics/blog/',
			icon: 'Ст',
			matchPrefixes: ['/admin/statistics/'],
			children: [
				new AdminMenuItem(
					id: 'statistics.blog',
					label: 'Блог',
					href: '/admin/statistics/blog/',
					matchPrefixes: ['/admin/statistics/blog/'],
				),
			],
		));

		$event->addItem(new AdminMenuItem(
			id: 'content',
			label: 'Контент',
			href: '/admin/projects/',
			icon: 'Кн',
			matchPrefixes: ['/admin/projects/', '/admin/content/'],
			children: [
				new AdminMenuItem(
					id: 'projects',
					label: 'Проекты',
					href: '/admin/projects/',
					matchPrefixes: ['/admin/projects/', '/admin/content/tags/'],
					children: [
						new AdminMenuItem(
							id: 'projects.list',
							label: 'Список проектов',
							href: '/admin/projects/',
							matchPrefixes: ['/admin/projects/'],
						),
						new AdminMenuItem(
							id: 'projects.tags',
							label: 'Теги проектов',
							href: '/admin/content/tags/',
							matchPrefixes: ['/admin/content/tags/'],
						),
					],
				),
				new AdminMenuItem(
					id: 'blog',
					label: 'Блог',
					href: '/admin/content/blog/',
					matchPrefixes: ['/admin/content/blog/'],
					children: [
						new AdminMenuItem(
							id: 'blog.rubrics',
							label: 'Рубрики',
							href: '/admin/content/blog/rubrics/',
							matchPrefixes: ['/admin/content/blog/rubrics/'],
							matchExact: ['/admin/content/blog/'],
						),
						new AdminMenuItem(
							id: 'blog.articles',
							label: 'Статьи',
							href: '/admin/content/blog/articles/',
							matchPrefixes: ['/admin/content/blog/articles/'],
						),
						new AdminMenuItem(
							id: 'blog.comments',
							label: 'Комментарии',
							href: '/admin/content/blog/comments/',
							matchPrefixes: ['/admin/content/blog/comments/'],
						),
					],
				),
				new AdminMenuItem(
					id: 'gallery',
					label: 'Галерея',
					href: '/admin/content/gallery/',
					matchPrefixes: ['/admin/content/gallery/'],
				),
			],
		));

		$event->addItem(new AdminMenuItem(
			id: 'seo',
			label: 'SEO',
			href: '/admin/seo/',
			icon: 'SE',
			matchPrefixes: ['/admin/seo/'],
		));

		$event->addItem(new AdminMenuItem(
			id: 'users',
			label: 'Пользователи',
			href: '/admin/users/',
			icon: 'По',
			matchPrefixes: ['/admin/users/'],
			children: [
				new AdminMenuItem(
					id: 'users.list',
					label: 'Список пользователей',
					href: '/admin/users/',
					matchExact: ['/admin/users/'],
				),
				new AdminMenuItem(
					id: 'users.roles',
					label: 'Пользовательские роли',
					href: '/admin/users/roles/',
					matchPrefixes: ['/admin/users/roles/'],
				),
			],
		));

		$event->addItem(new AdminMenuItem(
			id: 'resume',
			label: 'Резюме',
			href: '/admin/resume/experience/',
			icon: 'Рз',
			matchPrefixes: ['/admin/resume/'],
			children: [
				new AdminMenuItem(
					id: 'resume.experience',
					label: 'Опыт работы',
					href: '/admin/resume/experience/',
					matchPrefixes: ['/admin/resume/'],
				),
			],
		));

		$event->addItem(new AdminMenuItem(
			id: 'development',
			label: 'Разработка',
			href: '/admin/development/sql/',
			icon: 'Рд',
			matchPrefixes: ['/admin/development/'],
			children: [
				new AdminMenuItem(
					id: 'development.sql',
					label: 'SQL запросы',
					href: '/admin/development/sql/',
					matchPrefixes: ['/admin/development/sql/'],
				),
				new AdminMenuItem(
					id: 'development.todo',
					label: 'To Do List',
					href: '/admin/development/todo/',
					matchPrefixes: ['/admin/development/todo/'],
				),
				new AdminMenuItem(
					id: 'development.repository',
					label: 'Репозиторий',
					href: '/admin/development/repository/',
					matchPrefixes: ['/admin/development/repository/'],
				),
			],
		));

		$event->addItem(new AdminMenuItem(
			id: 'settings',
			label: 'Настройки',
			href: '/admin/settings/',
			icon: 'На',
			matchPrefixes: ['/admin/settings/'],
			children: [
				new AdminMenuItem(
					id: 'settings.configs',
					label: 'Конфиги',
					href: '/admin/settings/configs/',
					matchPrefixes: ['/admin/settings/configs/'],
				),
				new AdminMenuItem(
					id: 'settings.templates',
					label: 'Шаблоны',
					href: '/admin/settings/templates/',
					matchPrefixes: ['/admin/settings/templates/'],
				),
				new AdminMenuItem(
					id: 'settings.cron',
					label: 'Cron задачи',
					href: '/admin/settings/cron/',
					matchPrefixes: ['/admin/settings/cron/'],
				),
				new AdminMenuItem(
					id: 'settings.backup',
					label: 'Резервное копирование',
					href: '/admin/settings/backup/',
					matchPrefixes: ['/admin/settings/backup/'],
					children: [
						new AdminMenuItem(
							id: 'settings.backup.create',
							label: 'Создание копии',
							href: '/admin/settings/backup/create/',
							matchPrefixes: ['/admin/settings/backup/create/'],
						),
						new AdminMenuItem(
							id: 'settings.backup.list',
							label: 'Список копий',
							href: '/admin/settings/backup/list/',
							matchPrefixes: ['/admin/settings/backup/list/'],
						),
					],
				),
			],
		));
	}
}
