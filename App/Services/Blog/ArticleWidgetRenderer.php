<?php

namespace App\Services\Blog;

use DOMDocument;
use DOMElement;
use Modules\Main\AssetLoader;

class ArticleWidgetRenderer
{
	public function __construct(private readonly WidgetCatalog $catalog)
	{
	}

	public function hydrate(string $html): string
	{
		$html = trim($html);
		if ($html === '') {
			return '';
		}

		if (!class_exists(DOMDocument::class)) {
			return $this->hydrateWithoutDom($html);
		}

		$document = new DOMDocument('1.0', 'UTF-8');
		$previousUseErrors = libxml_use_internal_errors(true);
		$document->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		libxml_clear_errors();
		libxml_use_internal_errors($previousUseErrors);

		$root = $document->documentElement;
		if (!$root instanceof DOMElement) {
			return $html;
		}

		$nodes = $root->getElementsByTagName('div');
		for ($index = $nodes->length - 1; $index >= 0; $index--) {
			$element = $nodes->item($index);
			if (!$element instanceof DOMElement || !$this->isWidgetNode($element)) {
				continue;
			}

			$widget = $this->catalog->find(strtolower(trim($element->getAttribute('data-widget'))));
			if ($widget === null) {
				continue;
			}

			$this->enqueue($widget);
			$this->replaceInnerHtml($document, $element, $widget->html());
		}

		$result = '';
		foreach ($root->childNodes as $childNode) {
			$result .= $document->saveHTML($childNode);
		}

		return trim($result);
	}

	private function isWidgetNode(DOMElement $element): bool
	{
		$class = trim($element->getAttribute('class'));
		$names = preg_split('/\s+/', $class) ?: [];

		return in_array('blog-widget', $names, true);
	}

	private function replaceInnerHtml(DOMDocument $document, DOMElement $element, string $markup): void
	{
		while ($element->firstChild !== null) {
			$element->removeChild($element->firstChild);
		}

		$markup = trim($markup);
		if ($markup === '') {
			return;
		}

		$fragmentDocument = new DOMDocument('1.0', 'UTF-8');
		$previousUseErrors = libxml_use_internal_errors(true);
		$fragmentDocument->loadHTML('<?xml encoding="UTF-8"><div>' . $markup . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		libxml_clear_errors();
		libxml_use_internal_errors($previousUseErrors);

		$wrapper = $fragmentDocument->documentElement;
		if (!$wrapper instanceof DOMElement) {
			return;
		}

		foreach (iterator_to_array($wrapper->childNodes) as $child) {
			$element->appendChild($document->importNode($child, true));
		}
	}

	private function enqueue(WidgetDefinition $widget): void
	{
		$loader = AssetLoader::getInstance();
		if ($widget->cssUrl !== '') {
			$loader->addCss($widget->cssUrl);
		}
		if ($widget->jsUrl !== '') {
			$loader->addJs($widget->jsUrl);
		}
	}

	private function hydrateWithoutDom(string $html): string
	{
		return preg_replace_callback(
			'/<div\b(?=[^>]*\bblog-widget\b)([^>]*)>(.*?)<\/div>/is',
			function (array $matches): string {
				if (preg_match('/data-widget=(["\'])([a-z0-9-]+)\1/i', $matches[1], $idMatch) !== 1) {
					return $matches[0];
				}

				$widget = $this->catalog->find(strtolower($idMatch[2]));
				if ($widget === null) {
					return $matches[0];
				}

				$this->enqueue($widget);

				return '<div class="blog-widget" data-widget="' . htmlspecialchars($widget->id, ENT_QUOTES) . '">' . $widget->html() . '</div>';
			},
			$html
		) ?? $html;
	}
}
