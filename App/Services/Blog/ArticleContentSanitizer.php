<?php

namespace App\Services\Blog;

use DOMDocument;
use DOMElement;
use DOMNode;

class ArticleContentSanitizer
{
	private const ALLOWED_TAGS = [
		'a',
		'b',
		'blockquote',
		'br',
		'code',
		'em',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'i',
		'img',
		'li',
		'mark',
		'ol',
		'p',
		'pre',
		's',
		'span',
		'strong',
		'sub',
		'sup',
		'table',
		'tbody',
		'td',
		'th',
		'thead',
		'tr',
		'u',
		'ul',
		'wbr',
	];

	public function sanitize(string $html): string
	{
		$html = trim($html);
		if ($html === '') {
			return '';
		}

		if (!class_exists(DOMDocument::class)) {
			return $this->sanitizeWithoutDom($html);
		}

		$document = new DOMDocument('1.0', 'UTF-8');
		$previousUseErrors = libxml_use_internal_errors(true);
		$document->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		libxml_clear_errors();
		libxml_use_internal_errors($previousUseErrors);

		$root = $document->documentElement;
		if (!$root instanceof DOMElement) {
			return '';
		}

		$this->sanitizeNode($root);

		$result = '';
		foreach ($root->childNodes as $childNode) {
			$result .= $document->saveHTML($childNode);
		}

		return trim($result);
	}

	private function sanitizeNode(DOMNode $node): void
	{
		for ($child = $node->firstChild; $child !== null;) {
			$next = $child->nextSibling;

			if ($child instanceof DOMElement) {
				$tagName = strtolower($child->tagName);
				if (!in_array($tagName, self::ALLOWED_TAGS, true)) {
					$this->unwrapNode($child);
				} else {
					$this->sanitizeAttributes($child, $tagName);
					$this->sanitizeNode($child);
				}
			}

			$child = $next;
		}
	}

	private function unwrapNode(DOMElement $element): void
	{
		$parent = $element->parentNode;
		if ($parent === null) {
			return;
		}

		while ($element->firstChild !== null) {
			$parent->insertBefore($element->firstChild, $element);
		}

		$parent->removeChild($element);
	}

	private function sanitizeAttributes(DOMElement $element, string $tagName): void
	{
		$allowedAttributes = match ($tagName) {
			'a' => ['href', 'title', 'target', 'rel'],
			'img' => ['src', 'alt', 'title'],
			'mark' => ['style'],
			'span' => ['style', 'class'],
			'td', 'th' => ['colspan', 'rowspan'],
			default => [],
		};

		foreach (iterator_to_array($element->attributes) as $attribute) {
			if (!in_array($attribute->name, $allowedAttributes, true)) {
				$element->removeAttribute($attribute->name);
			}
		}

		if ($tagName === 'a') {
			$this->sanitizeLinkAttributes($element);
		}

		if ($tagName === 'img') {
			$this->sanitizeImageAttributes($element);
		}

		if ($tagName === 'mark' || $tagName === 'span') {
			$this->sanitizeStyleAttribute($element);
		}

		if ($tagName === 'span') {
			$this->sanitizeSpanClass($element);
		}

		if ($tagName === 'td' || $tagName === 'th') {
			$this->sanitizeTableCellAttributes($element);
		}
	}

	private function sanitizeTableCellAttributes(DOMElement $element): void
	{
		foreach (['colspan', 'rowspan'] as $attributeName) {
			$value = trim($element->getAttribute($attributeName));
			if ($value === '' || preg_match('/^[1-9][0-9]{0,2}$/', $value) !== 1) {
				$element->removeAttribute($attributeName);
			}
		}
	}

	private function sanitizeLinkAttributes(DOMElement $element): void
	{
		$href = trim($element->getAttribute('href'));
		if (!$this->isAllowedUrl($href)) {
			$element->removeAttribute('href');
		}

		if ($element->getAttribute('target') === '_blank') {
			$element->setAttribute('rel', 'noopener noreferrer');
			return;
		}

		$element->removeAttribute('target');
		$element->removeAttribute('rel');
	}

	private function sanitizeImageAttributes(DOMElement $element): void
	{
		$src = trim($element->getAttribute('src'));
		if (!$this->isAllowedImageUrl($src)) {
			$this->unwrapNode($element);
		}
	}

	private function sanitizeSpanClass(DOMElement $element): void
	{
		$class = trim($element->getAttribute('class'));
		if ($class === '') {
			$element->removeAttribute('class');
			return;
		}

		$allowed = [];
		foreach (preg_split('/\s+/', $class) ?: [] as $name) {
			if (in_array($name, ['blog-wrap', 'blog-nowrap'], true)) {
				$allowed[] = $name;
			}
		}

		if ($allowed === []) {
			$element->removeAttribute('class');
			return;
		}

		$element->setAttribute('class', implode(' ', array_unique($allowed)));
	}

	private function sanitizeStyleAttribute(DOMElement $element): void
	{
		$style = trim($element->getAttribute('style'));
		if ($style === '') {
			$element->removeAttribute('style');
			return;
		}

		$allowedRules = [];
		foreach (explode(';', $style) as $rule) {
			[$property, $value] = array_pad(explode(':', $rule, 2), 2, '');
			$property = strtolower(trim($property));
			$value = trim($value);

			if ($property === 'white-space' && $value === 'nowrap') {
				$allowedRules[] = 'white-space: nowrap';
				continue;
			}

			if (!in_array($property, ['color', 'background-color'], true) || !$this->isAllowedCssColor($value)) {
				continue;
			}

			$allowedRules[] = $property . ': ' . $value;
		}

		if ($allowedRules === []) {
			$element->removeAttribute('style');
			return;
		}

		$element->setAttribute('style', implode('; ', $allowedRules));
	}

	private function isAllowedUrl(string $url): bool
	{
		if ($url === '') {
			return false;
		}

		return str_starts_with($url, '/')
			|| str_starts_with($url, '#')
			|| preg_match('~^https?://~i', $url) === 1
			|| preg_match('~^mailto:[^\\s]+@[^\\s]+$~i', $url) === 1;
	}

	private function isAllowedImageUrl(string $url): bool
	{
		if ($url === '') {
			return false;
		}

		return str_starts_with($url, '/upload/articles/')
			|| str_starts_with($url, '/upload/images/blog/articles/');
	}

	private function isAllowedCssColor(string $value): bool
	{
		return preg_match('~^(#[0-9a-f]{3,8}|rgba?\\([0-9\\s,.%]+\\)|[a-z]+)$~i', $value) === 1;
	}

	private function sanitizeWithoutDom(string $html): string
	{
		$cleanHtml = strip_tags($html, '<p><h1><h2><h3><h4><h5><h6><img><a><blockquote><pre><code><strong><em><b><i><u><s><mark><span><br><wbr><ul><ol><li><table><thead><tbody><tr><th><td><sup><sub>');
		$cleanHtml = preg_replace('~\\s+on[a-z]+\\s*=\\s*("[^"]*"|\\\'[^\\\']*\\\'|[^\\s>]+)~i', '', $cleanHtml) ?? '';
		$cleanHtml = preg_replace('~\\s+(href|src)\\s*=\\s*("|\')\\s*javascript:[^"\']*\\2~i', '', $cleanHtml) ?? '';

		return trim($cleanHtml);
	}
}
