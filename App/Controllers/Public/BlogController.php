<?php
namespace Controllers\Public;

use App\Services\Seo\SeoContext;
use Modules\Main\Auth;
use Modules\Main\BaseController;
use Modules\Main\Template;

class BlogController extends BaseController
{
	public function index(): void
	{
		$this->setSeo(SeoContext::page('blog'));
		Template::getInstance()->setParam('title', 'Блог');
		Template::getInstance()->setParam('subtitle', 'Темы статей и заметок');
		Template::getInstance()->setParam('show_contact_cta', false);

		Template::getInstance()->showHeader();
		$this->render('index', [
			'topics' => $this->getTopics(),
			'is_admin' => Auth::getInstance()->isAdmin(),
		]);
		Template::getInstance()->showFooter();
	}

	public function topic(string $topic): void
	{
		$topicData = $this->findTopic($topic);

		$this->setSeo(SeoContext::custom('/blog/' . $topic . '/', [
			'title' => $topicData['name'] ?? 'Тема не найдена',
			'description' => $topicData['description'] ?? 'Тестовая тема блога.',
			'robots_index' => $topicData !== null,
		]));

		Template::getInstance()->setParam('title', $topicData['name'] ?? 'Тема не найдена');
		Template::getInstance()->setParam('subtitle', 'Статьи выбранной темы');
		Template::getInstance()->setParam('show_contact_cta', false);

		Template::getInstance()->showHeader();
		$this->render('topic', [
			'topic' => $topicData,
			'is_admin' => Auth::getInstance()->isAdmin(),
		]);
		Template::getInstance()->showFooter();
	}

	public function detail(string $topic, string $article): void
	{
		$topicData = $this->findTopic($topic);
		$articleData = $topicData !== null ? $this->findArticle($topicData, $article) : null;

		$this->setSeo(SeoContext::custom('/blog/' . $topic . '/' . $article . '/', [
			'title' => $articleData['title'] ?? 'Статья не найдена',
			'description' => $articleData['preview'] ?? 'Тестовая статья блога.',
			'robots_index' => $articleData !== null,
		]));

		Template::getInstance()->setParam('title', $articleData['title'] ?? 'Статья не найдена');
		Template::getInstance()->setParam('subtitle', 'Детальная страница статьи');
		Template::getInstance()->setParam('show_contact_cta', false);

		Template::getInstance()->showHeader();
		$this->render('detail', [
			'topic' => $topicData,
			'article' => $articleData,
			'is_admin' => Auth::getInstance()->isAdmin(),
		]);
		Template::getInstance()->showFooter();
	}

	private function findTopic(string $slug): ?array
	{
		foreach ($this->getTopics() as $topic) {
			if ($topic['slug'] === $slug) {
				return $topic;
			}
		}

		return null;
	}

	private function findArticle(array $topic, string $slug): ?array
	{
		foreach ($topic['articles'] as $article) {
			if ($article['slug'] === $slug) {
				return $article;
			}
		}

		return null;
	}

	private function getTopics(): array
	{
		return [
			[
				'name' => 'Видеоигры',
				'slug' => 'videogames',
				'image' => '/upload/images/blog/topic-videogames.svg',
				'description' => 'Заметки о сюжетах, механиках, жанрах и личных впечатлениях от игр.',
				'articles' => [
					[
						'title' => 'Почему короткие игры снова цепляют',
						'slug' => 'short-games',
						'image' => '/upload/images/blog/article-short-games.svg',
						'date' => '18 июля 2026',
						'rating' => 8,
						'preview' => 'Небольшая заметка о том, почему компактные игровые истории часто запоминаются сильнее огромных миров.',
						'content' => [
							'Короткие игры выигрывают за счет темпа. Они быстрее показывают идею, не перегружают игрока лишними системами и оставляют после себя цельное впечатление.',
							'В таких проектах особенно заметны работа с атмосферой, визуальным ритмом и точностью игровых ситуаций.',
						],
						'comments' => [
							['author' => 'Алексей', 'text' => 'Люблю такие проекты: прошел за вечер, но думаю о них неделю.'],
							['author' => 'Мария', 'text' => 'Согласна, иногда меньше контента означает больше эмоций.'],
						],
					],
					[
						'title' => 'Что делает игровую механику честной',
						'slug' => 'fair-game-mechanics',
						'image' => '/upload/images/blog/article-game-mechanics.svg',
						'date' => '25 июля 2026',
						'rating' => 9,
						'preview' => 'Разбираю, почему хорошие правила должны быть понятными, последовательными и уважать время игрока.',
						'content' => [
							'Честная механика не обязана быть легкой. Важно, чтобы игрок понимал причину ошибки и видел путь к улучшению.',
							'Когда игра стабильно соблюдает собственные правила, сложность воспринимается как вызов, а не как случайность.',
						],
						'comments' => [
							['author' => 'Игорь', 'text' => 'Хорошее наблюдение про понятную причину поражения.'],
							['author' => 'Светлана', 'text' => 'Это особенно заметно в платформерах и тактических играх.'],
						],
					],
				],
			],
			[
				'name' => 'IT и программирование',
				'slug' => 'it-programming',
				'image' => '/upload/images/blog/topic-programming.svg',
				'description' => 'Практические мысли о разработке, архитектуре, PHP и рабочих процессах.',
				'articles' => [
					[
						'title' => 'Минимальные правки как навык разработчика',
						'slug' => 'minimal-changes',
						'image' => '/upload/images/blog/article-minimal-changes.svg',
						'date' => '12 июля 2026',
						'rating' => 10,
						'preview' => 'О том, почему точечные изменения часто надежнее больших рефакторингов без необходимости.',
						'content' => [
							'Минимальная правка хороша не потому, что она маленькая, а потому что она уважает уже работающую систему.',
							'Сначала стоит понять текущие границы проекта, а уже потом выбирать место для изменения.',
						],
						'comments' => [
							['author' => 'Дмитрий', 'text' => 'Очень близко к реальной командной разработке.'],
							['author' => 'Ольга', 'text' => 'Да, особенно когда проект уже в продакшене.'],
						],
					],
					[
						'title' => 'Зачем PHP-проекту простая структура',
						'slug' => 'simple-php-structure',
						'image' => '/upload/images/blog/article-php-structure.svg',
						'date' => '29 июля 2026',
						'rating' => 8,
						'preview' => 'Коротко о том, как понятные контроллеры, модели и шаблоны помогают быстрее развивать сайт.',
						'content' => [
							'Простая структура снижает стоимость каждого следующего изменения. Разработчик быстрее находит нужный участок и меньше рискует задеть лишнее.',
							'Даже без тяжелого фреймворка проект может оставаться аккуратным, если в нем есть понятные соглашения.',
						],
						'comments' => [
							['author' => 'Никита', 'text' => 'Хороший аргумент в пользу умеренности в архитектуре.'],
							['author' => 'Елена', 'text' => 'Главное, чтобы соглашения действительно соблюдались.'],
						],
					],
				],
			],
		];
	}
}
