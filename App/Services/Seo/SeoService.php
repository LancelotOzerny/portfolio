<?php

namespace App\Services\Seo;

use App\Services\Seo\Config\SeoConfig;
use Models\SeoMetaModel;

class SeoService
{
	public function __construct(
		private readonly SeoConfig $config = new SeoConfig(),
		private readonly SeoMetaModel $model = new SeoMetaModel(),
	) {
	}

	public function resolve(SeoContext $context): SeoMeta
	{
		$site = $this->config->getSite();
		$db = $this->model->findByTarget($context->getTargetType(), $context->getTargetKey());

		$title = trim((string) ($site['default_title'] ?? ''));
		$description = trim((string) ($site['default_description'] ?? ''));
		$ogImage = $this->normalizeNullableString($site['default_og_image'] ?? null);
		$index = true;
		$follow = true;
		$canonical = '';
		$ogTitle = '';
		$ogDescription = '';
		$ogType = $context->getTargetType() === 'project' ? 'article' : 'website';

		if ($context->getTargetType() === 'page') {
			$page = $this->config->getPage($context->getTargetKey()) ?? [];
			$title = $this->pickNonEmpty($title, (string) ($page['title'] ?? ''));
			$description = $this->pickNonEmpty($description, (string) ($page['description'] ?? ''));
			$ogTitle = $this->pickNonEmpty($ogTitle, (string) ($page['og_title'] ?? ''));
			$ogDescription = $this->pickNonEmpty($ogDescription, (string) ($page['og_description'] ?? ''));
			$ogImage = $this->pickNonEmpty($ogImage, $this->normalizeNullableString($page['og_image'] ?? null));
		}

		$entity = $context->getEntityData();
		$title = $this->pickNonEmpty($title, (string) ($entity['title'] ?? ''));
		$description = $this->pickNonEmpty($description, (string) ($entity['description'] ?? ''));
		$ogTitle = $this->pickNonEmpty($ogTitle, (string) ($entity['og_title'] ?? ''));
		$ogDescription = $this->pickNonEmpty($ogDescription, (string) ($entity['og_description'] ?? ''));
		$ogImage = $this->pickNonEmpty($ogImage, $this->normalizeNullableString($entity['og_image'] ?? null));

		if (array_key_exists('robots_index', $entity)) {
			$index = (bool) $entity['robots_index'];
		}
		if (array_key_exists('robots_follow', $entity)) {
			$follow = (bool) $entity['robots_follow'];
		}

		$hasManualTitle = $db !== null && trim((string) ($db->title ?? '')) !== '';
		if (!$hasManualTitle) {
			$title = $this->applyTitleTemplate($title, $site);
		}

		if ($db !== null) {
			$title = $this->pickNonEmpty($title, (string) ($db->title ?? ''));
			$description = $this->pickNonEmpty($description, (string) ($db->description ?? ''));
			$canonical = trim((string) ($db->canonical_url ?? ''));
			$ogTitle = trim((string) ($db->og_title ?? ''));
			$ogDescription = trim((string) ($db->og_description ?? ''));
			$ogImage = $this->pickNonEmpty($ogImage, $this->normalizeNullableString($db->og_image ?? null));
			$index = (int) ($db->robots_index ?? 1) === 1;
			$follow = (int) ($db->robots_follow ?? 1) === 1;
		}

		if ($canonical === '') {
			$canonical = $this->buildCanonical($site, $context->getPath());
		}

		if ($ogTitle === '') {
			$ogTitle = $title;
		}
		if ($ogDescription === '') {
			$ogDescription = $description;
		}

		return new SeoMeta(
			title: $title,
			description: $description,
			canonical: $this->toAbsoluteUrl($site, $canonical),
			index: $index,
			follow: $follow,
			ogTitle: $ogTitle,
			ogDescription: $ogDescription,
			ogImage: $this->toAbsoluteUrl($site, $ogImage),
			ogType: $ogType,
			siteName: (string) ($site['name'] ?? ''),
		);
	}

	/**
	 * @param array<string, mixed> $site
	 */
	private function applyTitleTemplate(string $title, array $site): string
	{
		$title = trim($title);
		$siteName = trim((string) ($site['name'] ?? ''));
		$template = (string) ($site['title_template'] ?? '%title% — %site_name%');

		if ($title === '' || $siteName === '') {
			return $title !== '' ? $title : $siteName;
		}

		$formattedSuffix = str_replace('%site_name%', $siteName, ' — %site_name%');
		if ($formattedSuffix !== '' && str_ends_with($title, $formattedSuffix)) {
			return $title;
		}

		if ($title === $siteName) {
			return $title;
		}

		return str_replace(
			['%title%', '%site_name%'],
			[$title, $siteName],
			$template
		);
	}

	/**
	 * @param array<string, mixed> $site
	 */
	private function buildCanonical(array $site, string $path): string
	{
		$domain = rtrim((string) ($site['domain'] ?? ''), '/');
		$path = '/' . ltrim($path, '/');
		if ($path !== '/' && str_ends_with($path, '/')) {
			$path = rtrim($path, '/');
		}

		return $domain !== '' ? $domain . $path : $path;
	}

	/**
	 * @param array<string, mixed> $site
	 */
	private function toAbsoluteUrl(array $site, ?string $url): ?string
	{
		$url = trim((string) $url);
		if ($url === '') {
			return null;
		}

		if (preg_match('~^https?://~i', $url) === 1) {
			return $url;
		}

		$domain = rtrim((string) ($site['domain'] ?? ''), '/');
		if ($domain === '') {
			return $url;
		}

		return $domain . '/' . ltrim($url, '/');
	}

	private function pickNonEmpty(?string $current, ?string $next): string
	{
		$next = trim((string) $next);
		if ($next !== '') {
			return $next;
		}

		return trim((string) $current);
	}

	private function normalizeNullableString(mixed $value): ?string
	{
		$value = trim((string) $value);

		return $value !== '' ? $value : null;
	}
}
