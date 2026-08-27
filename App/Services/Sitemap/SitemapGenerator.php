<?php

namespace App\Services\Sitemap;

use App\Services\Blog\BlogArticlePublicationService;
use App\Services\Blog\SymbolicCodeService;
use App\Services\Seo\Config\SeoConfig;
use Models\BlogArticlesModel;
use Models\BlogTopicsModel;
use Models\ProjectsModel;
use Modules\Main\App;
use RuntimeException;
use Throwable;

final class SitemapGenerator
{
	public function __construct(
		private readonly SeoConfig $seoConfig = new SeoConfig(),
		private readonly SymbolicCodeService $codes = new SymbolicCodeService(),
		private readonly BlogArticlePublicationService $publication = new BlogArticlePublicationService(),
	) {
	}

	public function generate(): int
	{
		$urls = $this->collectUrls();
		$this->writeFile('sitemap.xml', $this->buildSitemapXml($urls));
		$this->writeFile('robots.txt', $this->buildRobotsTxt());

		return count($urls);
	}

	/**
	 * @return list<array{loc: string, lastmod?: string, changefreq: string, priority: string}>
	 */
	private function collectUrls(): array
	{
		$urls = [];
		$seen = [];

		foreach ($this->staticPages() as $page) {
			$this->pushUrl($urls, $seen, $page['path'], $page['changefreq'], $page['priority']);
		}

		foreach ($this->topicPages() as $page) {
			$this->pushUrl($urls, $seen, $page['path'], 'weekly', '0.7', $page['lastmod'] ?? null);
		}

		foreach ($this->articlePages() as $page) {
			$this->pushUrl($urls, $seen, $page['path'], 'weekly', '0.6', $page['lastmod'] ?? null);
		}

		foreach ($this->projectPages() as $page) {
			$this->pushUrl($urls, $seen, $page['path'], 'monthly', '0.7');
		}

		return $urls;
	}

	/**
	 * @return list<array{path: string, changefreq: string, priority: string}>
	 */
	private function staticPages(): array
	{
		$defaults = [
			'home' => ['changefreq' => 'weekly', 'priority' => '1.0'],
			'about' => ['changefreq' => 'monthly', 'priority' => '0.8'],
			'portfolio' => ['changefreq' => 'weekly', 'priority' => '0.8'],
			'blog' => ['changefreq' => 'daily', 'priority' => '0.8'],
			'certificates' => ['changefreq' => 'monthly', 'priority' => '0.5'],
			'contacts' => ['changefreq' => 'monthly', 'priority' => '0.6'],
			'cookies' => ['changefreq' => 'yearly', 'priority' => '0.2'],
		];

		$pages = [];
		foreach ($this->seoConfig->getPages() as $key => $page) {
			$path = trim((string) ($page['path'] ?? ''));
			if ($path === '') {
				continue;
			}

			$meta = $defaults[$key] ?? ['changefreq' => 'monthly', 'priority' => '0.5'];
			$pages[] = [
				'path' => $path,
				'changefreq' => $meta['changefreq'],
				'priority' => $meta['priority'],
			];
		}

		return $pages;
	}

	/**
	 * @return list<array{path: string, lastmod?: string}>
	 */
	private function topicPages(): array
	{
		try {
			$topics = (new BlogTopicsModel())->findEnabled();
		} catch (Throwable) {
			return [];
		}

		$pages = [];
		foreach ($topics as $topic) {
			$topicId = (int) ($topic->id ?? 0);
			$segment = $this->codes->resolvePublicSegment((string) ($topic->code ?? ''), $topicId);
			if ($segment === '') {
				continue;
			}

			$pages[] = ['path' => '/blog/' . rawurlencode($segment) . '/'];
		}

		return $pages;
	}

	/**
	 * @return list<array{path: string, lastmod?: string}>
	 */
	private function articlePages(): array
	{
		try {
			$topics = (new BlogTopicsModel())->findEnabled();
		} catch (Throwable) {
			return [];
		}

		$articlesModel = new BlogArticlesModel();
		$pages = [];

		foreach ($topics as $topic) {
			$topicId = (int) ($topic->id ?? 0);
			$topicSegment = $this->codes->resolvePublicSegment((string) ($topic->code ?? ''), $topicId);
			if ($topicId <= 0 || $topicSegment === '') {
				continue;
			}

			try {
				$articles = $articlesModel->findByTopicId($topicId, true);
			} catch (Throwable) {
				continue;
			}

			foreach ($articles as $article) {
				$articleId = (int) ($article->id ?? 0);
				$articleSegment = $this->codes->resolvePublicSegment((string) ($article->code ?? ''), $articleId);
				if ($articleId <= 0 || $articleSegment === '') {
					continue;
				}

				$page = [
					'path' => '/blog/' . rawurlencode($topicSegment) . '/' . rawurlencode($articleSegment) . '/',
				];
				$lastmod = $this->articleLastmod($article);
				if ($lastmod !== null) {
					$page['lastmod'] = $lastmod;
				}

				$pages[] = $page;
			}
		}

		return $pages;
	}

	/**
	 * @return list<array{path: string}>
	 */
	private function projectPages(): array
	{
		try {
			$projects = (new ProjectsModel())->findAll();
		} catch (Throwable) {
			return [];
		}

		$pages = [];
		foreach ($projects as $project) {
			$projectId = (int) ($project->id ?? 0);
			if ($projectId <= 0) {
				continue;
			}

			$pages[] = ['path' => '/portfolio/' . $projectId . '/'];
		}

		return $pages;
	}

	private function articleLastmod(object $article): ?string
	{
		$datetime = $this->publication->getPublicationDatetime($article);
		if ($datetime === null || $datetime === '') {
			$datetime = trim((string) ($article->created_at ?? ''));
		}

		$timestamp = strtotime($datetime);
		if ($timestamp === false) {
			return null;
		}

		return date('Y-m-d', $timestamp);
	}

	/**
	 * @param list<array{loc: string, lastmod?: string, changefreq: string, priority: string}> $urls
	 */
	private function pushUrl(array &$urls, array &$seen, string $path, string $changefreq, string $priority, ?string $lastmod = null): void
	{
		$loc = $this->absoluteUrl($path);
		if ($loc === '' || isset($seen[$loc])) {
			return;
		}

		$seen[$loc] = true;
		$entry = [
			'loc' => $loc,
			'changefreq' => $changefreq,
			'priority' => $priority,
		];
		if ($lastmod !== null && $lastmod !== '') {
			$entry['lastmod'] = $lastmod;
		}

		$urls[] = $entry;
	}

	private function absoluteUrl(string $path): string
	{
		$domain = rtrim((string) ($this->seoConfig->getSite()['domain'] ?? ''), '/');
		if ($domain === '') {
			return '';
		}

		if ($path === '' || $path[0] !== '/') {
			$path = '/' . ltrim($path, '/');
		}

		return $domain . $path;
	}

	/**
	 * @param list<array{loc: string, lastmod?: string, changefreq: string, priority: string}> $urls
	 */
	private function buildSitemapXml(array $urls): string
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		foreach ($urls as $url) {
			$xml .= "\t<url>\n";
			$xml .= "\t\t<loc>" . $this->escapeXml($url['loc']) . "</loc>\n";
			if (isset($url['lastmod'])) {
				$xml .= "\t\t<lastmod>" . $this->escapeXml($url['lastmod']) . "</lastmod>\n";
			}
			$xml .= "\t\t<changefreq>" . $this->escapeXml($url['changefreq']) . "</changefreq>\n";
			$xml .= "\t\t<priority>" . $this->escapeXml($url['priority']) . "</priority>\n";
			$xml .= "\t</url>\n";
		}

		$xml .= '</urlset>' . "\n";

		return $xml;
	}

	private function buildRobotsTxt(): string
	{
		$sitemap = $this->absoluteUrl('/sitemap.xml');
		$lines = [
			'User-agent: *',
			'Allow: /',
			'',
			'Disallow: /admin/',
			'Disallow: /api/',
			'',
		];
		if ($sitemap !== '') {
			$lines[] = 'Sitemap: ' . $sitemap;
			$lines[] = '';
		}

		return implode("\n", $lines);
	}

	private function writeFile(string $name, string $contents): void
	{
		$path = App::getInstance()->root . '/public_html/' . $name;
		$written = file_put_contents($path, $contents, LOCK_EX);
		if ($written === false) {
			throw new RuntimeException('Не удалось записать файл ' . $name);
		}
	}

	private function escapeXml(string $value): string
	{
		return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}
}
