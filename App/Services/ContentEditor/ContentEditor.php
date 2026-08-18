<?php

namespace App\Services\ContentEditor;

use App\Services\Blog\WidgetCatalog;
use App\Services\Blog\WidgetDefinition;
use App\Services\Security\CsrfService;
use Modules\Main\AssetLoader;

class ContentEditor
{
	/**
	 * @param array{
	 *     id?: string,
	 *     name?: string,
	 *     html?: string,
	 *     class?: string,
	 *     wrap?: bool,
	 *     widgets?: bool,
	 *     uploadUrl?: string,
	 *     uploadFileUrl?: string,
	 *     extraUploadFields?: array<string, string>
	 * } $options
	 */
	public function render(array $options = []): void
	{
		$this->registerAssets();

		$id = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($options['id'] ?? 'content-editor')) ?: 'content-editor';
		$name = (string) ($options['name'] ?? 'detail_text');
		$html = (string) ($options['html'] ?? '');
		$wrapperClass = trim('content-editor ' . (string) ($options['class'] ?? ''));
		$wrap = ($options['wrap'] ?? true) !== false;
		$holderId = $id . '-area';
		$inputId = $id . '-input';
		$blocksId = $id . '-blocks';

		$widgets = [];
		if (($options['widgets'] ?? true) !== false) {
			$catalog = new WidgetCatalog();
			$widgets = array_map(
				static fn (WidgetDefinition $widget): array => $widget->editorData(),
				$catalog->all()
			);
		}

		$extraUploadFields = [];
		if (is_array($options['extraUploadFields'] ?? null)) {
			foreach ($options['extraUploadFields'] as $key => $value) {
				$extraUploadFields[(string) $key] = (string) $value;
			}
		}

		$config = [
			'holderId' => $holderId,
			'inputId' => $inputId,
			'blocksId' => $blocksId,
			'initialHtml' => $html,
			'csrfToken' => (new CsrfService())->getToken(),
			'uploadUrl' => (string) ($options['uploadUrl'] ?? ''),
			'uploadFileUrl' => (string) ($options['uploadFileUrl'] ?? ''),
			'extraUploadFields' => $extraUploadFields,
			'widgets' => $widgets,
		];
		$configJson = json_encode(
			$config,
			JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		);

		if ($wrap) {
			echo '<div class="' . htmlspecialchars($wrapperClass) . '">';
		}
		?>
		<input id="<?= htmlspecialchars($inputId) ?>" type="hidden" name="<?= htmlspecialchars($name) ?>" value="<?= htmlspecialchars($html) ?>">
		<input id="<?= htmlspecialchars($blocksId) ?>" type="hidden" name="detail_blocks" value="">
		<div id="<?= htmlspecialchars($holderId) ?>" class="content-editor__area"></div>
		<script type="application/json" data-content-editor-config><?= $configJson ?></script>
		<?php
		if ($wrap) {
			echo '</div>';
		}
	}

	public function registerAssets(): void
	{
		$loader = AssetLoader::getInstance();
		$loader->addCss('/assets/css/content-editor.css');
		$loader->addJs('https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.8/dist/editorjs.umd.min.js');
		$loader->addJs('https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.8/dist/header.umd.min.js');
		$loader->addJs('https://cdn.jsdelivr.net/npm/@editorjs/list@1.10.0/dist/list.umd.min.js');
		$loader->addJs('https://cdn.jsdelivr.net/npm/@editorjs/image@2.10.3/dist/image.umd.min.js');
		$loader->addJs('https://cdn.jsdelivr.net/npm/@editorjs/quote@2.7.6/dist/quote.umd.min.js');
		$loader->addJs('https://cdn.jsdelivr.net/npm/@editorjs/code@2.9.3/dist/code.umd.min.js');
		$loader->addJs('/assets/js/content-editor.js');
	}
}
