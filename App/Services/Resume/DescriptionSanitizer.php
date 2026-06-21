<?php

namespace App\Services\Resume;

use DOMDocument;
use DOMElement;
use DOMNode;

class DescriptionSanitizer
{
	private const ALLOWED_TAGS = ['p', 'div', 'br', 'strong', 'b', 'em', 'i', 'u', 'ol', 'ul', 'li'];
	private const DROP_WITH_CONTENT_TAGS = ['script', 'style', 'iframe', 'object', 'svg', 'math'];

	public function sanitize(string $html): string
	{
		$html = trim($html);
		if ($html === '') {
			return '';
		}

		$previousState = libxml_use_internal_errors(true);
		$document = new DOMDocument('1.0', 'UTF-8');
		$document->loadHTML(
			'<?xml encoding="UTF-8"><div id="resume-description">' . $html . '</div>',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);

		$container = $document->getElementById('resume-description');
		if ($container === null) {
			libxml_clear_errors();
			libxml_use_internal_errors($previousState);
			return '';
		}

		$this->cleanChildren($container);
		$result = '';
		foreach ($container->childNodes as $child) {
			$result .= $document->saveHTML($child);
		}

		libxml_clear_errors();
		libxml_use_internal_errors($previousState);

		return trim($result);
	}

	private function cleanChildren(DOMNode $parent): void
	{
		foreach (iterator_to_array($parent->childNodes) as $node) {
			if ($node instanceof DOMElement) {
				$tagName = strtolower($node->tagName);
				if (in_array($tagName, self::DROP_WITH_CONTENT_TAGS, true)) {
					$node->parentNode?->removeChild($node);
					continue;
				}

				$this->cleanChildren($node);

				if (!in_array($tagName, self::ALLOWED_TAGS, true)) {
					$this->unwrap($node);
					continue;
				}

				while ($node->attributes->length > 0) {
					$node->removeAttributeNode($node->attributes->item(0));
				}
			}
		}
	}

	private function unwrap(DOMElement $element): void
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
}
