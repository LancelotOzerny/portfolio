<?php

namespace App\Services\Blog;

use DOMDocument;
use DOMElement;
use DOMNode;

class ArticleContentSanitizer
{
	private const ALLOWED_TAGS = [
		'a',
		'aside',
		'b',
		'blockquote',
		'br',
		'caption',
		'code',
		'details',
		'div',
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
		'summary',
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

	private const ALLOWED_CLASSES = [
		'span' => ['blog-wrap', 'blog-nowrap'],
		'table' => ['blog-table', 'blog-table_h-center', 'blog-table_v-middle'],
		'td' => ['blog-table-cell_h-center', 'blog-table-cell_v-middle'],
		'th' => ['blog-table-cell_h-center', 'blog-table-cell_v-middle'],
		'aside' => ['blog-alert', 'blog-alert_info', 'blog-alert_warning', 'blog-alert_danger', 'blog-alert_success'],
		'details' => ['blog-spoiler'],
		'div' => ['blog-widget'],
	];

	private const REQUIRED_CLASSES = [
		'aside' => 'blog-alert',
		'details' => 'blog-spoiler',
		'div' => 'blog-widget',
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
					if ($this->shouldUnwrapStyledBlock($child, $tagName)) {
						$this->unwrapNode($child);
					}
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
			'table', 'aside' => ['class'],
			'details' => ['class', 'open'],
			'div' => ['class', 'data-widget', 'data-widget-params'],
			'td', 'th' => ['colspan', 'rowspan', 'class'],
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

		if (isset(self::ALLOWED_CLASSES[$tagName])) {
			$this->sanitizeAllowedClass($element, self::ALLOWED_CLASSES[$tagName]);
		}

		if ($tagName === 'td' || $tagName === 'th') {
			$this->sanitizeTableCellAttributes($element);
		}

		if ($tagName === 'details') {
			$this->sanitizeDetailsAttributes($element);
		}

		if ($tagName === 'div') {
			$this->sanitizeWidgetAttributes($element);
		}
	}

	private function shouldUnwrapStyledBlock(DOMElement $element, string $tagName): bool
	{
		if (!isset(self::REQUIRED_CLASSES[$tagName])) {
			return false;
		}

		$class = trim($element->getAttribute('class'));
		$names = preg_split('/\s+/', $class) ?: [];

		return !in_array(self::REQUIRED_CLASSES[$tagName], $names, true);
	}

	private function sanitizeDetailsAttributes(DOMElement $element): void
	{
		if (!$element->hasAttribute('open')) {
			return;
		}

		$element->setAttribute('open', 'open');
	}

	private function sanitizeWidgetAttributes(DOMElement $element): void
	{
		$widget = strtolower(trim($element->getAttribute('data-widget')));
		if ($widget === '' || preg_match('/^[a-z0-9-]+$/', $widget) !== 1) {
			$element->removeAttribute('data-widget');
			$element->removeAttribute('data-widget-params');
			return;
		}

		$element->setAttribute('data-widget', $widget);

		$paramsJson = $this->sanitizeWidgetParamsJson($element->getAttribute('data-widget-params'));
		if ($paramsJson === '') {
			$element->removeAttribute('data-widget-params');
			return;
		}

		$element->setAttribute('data-widget-params', $paramsJson);
	}

	private const MAX_WIDGET_PARAM_STRING = 120;
	private const MAX_WIDGET_PARAM_LIST = 32;

	private function sanitizeWidgetParamsJson(string $raw): string
	{
		$decoded = json_decode($raw, true);
		if (!is_array($decoded) || array_is_list($decoded)) {
			return '';
		}

		$clean = $this->sanitizeWidgetParamMap($decoded, 0);
		if ($clean === []) {
			return '';
		}

		$encoded = json_encode($clean, JSON_UNESCAPED_UNICODE);
		return is_string($encoded) ? $encoded : '';
	}

	/**
	 * @param array<mixed> $source
	 * @return array<string, mixed>
	 */
	private function sanitizeWidgetParamMap(array $source, int $depth): array
	{
		$clean = [];
		foreach ($source as $key => $value) {
			if (!is_string($key) || preg_match('/^[a-z][a-z0-9_]*$/', $key) !== 1) {
				continue;
			}

			$sanitized = $this->sanitizeWidgetParamValue($value, $depth);
			if ($sanitized === null) {
				continue;
			}

			$clean[$key] = $sanitized;
		}

		return $clean;
	}

	private function sanitizeWidgetParamValue(mixed $value, int $depth): mixed
	{
		if (is_int($value) || is_float($value)) {
			$number = (float) $value;
			if (is_nan($number) || is_infinite($number)) {
				return null;
			}

			return $value;
		}

		if (is_string($value)) {
			$text = $this->sanitizeWidgetParamString($value);
			return $text === '' ? null : $text;
		}

		if (!is_array($value) || $depth > 0) {
			return null;
		}

		if (!array_is_list($value)) {
			return null;
		}

		$rows = [];
		foreach (array_slice($value, 0, self::MAX_WIDGET_PARAM_LIST) as $item) {
			if (!is_array($item) || array_is_list($item)) {
				continue;
			}

			$row = $this->sanitizeWidgetParamMap($item, $depth + 1);
			if ($row !== []) {
				$rows[] = $row;
			}
		}

		return $rows === [] ? null : $rows;
	}

	private function sanitizeWidgetParamString(string $value): string
	{
		$value = trim(strip_tags($value));
		$value = str_replace(['<', '>'], '', $value);
		$value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
		if (mb_strlen($value) > self::MAX_WIDGET_PARAM_STRING) {
			$value = mb_substr($value, 0, self::MAX_WIDGET_PARAM_STRING);
		}

		return $value;
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

	private function sanitizeAllowedClass(DOMElement $element, array $allowedNames): void
	{
		$class = trim($element->getAttribute('class'));
		if ($class === '') {
			$element->removeAttribute('class');
			return;
		}

		$allowed = [];
		foreach (preg_split('/\s+/', $class) ?: [] as $name) {
			if (in_array($name, $allowedNames, true)) {
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
		$cleanHtml = strip_tags($html, '<p><h1><h2><h3><h4><h5><h6><img><a><blockquote><pre><code><strong><em><b><i><u><s><mark><span><br><wbr><ul><ol><li><table><caption><thead><tbody><tr><th><td><sup><sub><aside><details><summary><div>');
		$cleanHtml = preg_replace('~\\s+on[a-z]+\\s*=\\s*("[^"]*"|\\\'[^\\\']*\\\'|[^\\s>]+)~i', '', $cleanHtml) ?? '';
		$cleanHtml = preg_replace('~\\s+(href|src)\\s*=\\s*("|\')\\s*javascript:[^"\']*\\2~i', '', $cleanHtml) ?? '';

		return trim($cleanHtml);
	}
}
