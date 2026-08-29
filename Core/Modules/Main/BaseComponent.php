<?php

namespace Modules\Main;

use App\Services\Component\ComponentSettingsStorage;
use App\Services\Site\EditModeService;

class BaseComponent
{
    protected array $params = [];
	private static bool $editAssetsRegistered = false;
	private static int $editInstanceCounter = 0;
	private string $editKey = '';
	private float $preparationTimeMs = 0.0;

    final public function __construct(array $params = [])
    {
		$startedAt = hrtime(true);
		$this->editKey = $this->resolveEditKey($params);
		$params = $this->mergeStoredParams($params);
		$this->prepareData($params);

		if (isset($params['template']))
		{
			$this->setParam('template', $params['template']);
		}
		else
		{
			$this->setParam('template', 'Default');
		}

		$this->preparationTimeMs = $this->elapsedMilliseconds($startedAt);
	}

    protected function prepareData(array $params = []) : void
    {
        $this->params = $params;
    }

    final public function setParam(string $key, $value) : static
    {
        $this->params[$key] = $value;
        return $this;
    }

    final public function getParam(string $key)
    {
        return $this->params[$key] ?? null;
    }

	final public function hasParam(string $key) : bool
	{
		return array_key_exists($key, $this->params);
	}

    final public function render() : void
    {
		$startedAt = hrtime(true);
		$classPath = $this->getClassPath();
		$template = $this->getParam('template');
        $templatePath = "{$classPath}/{$template}";
		$templatePath = str_replace('\\', '/', $templatePath);
		$viewPath = $templatePath . '/index.php';

		if (file_exists($path = $templatePath . '/script.js'))
		{
			$publicPath = str_replace('\\', '/', App::getInstance()->root . '/public_html');
			$path = str_replace($publicPath, '', $path);
			AssetLoader::getInstance()->addJs($path);
		}

		if (file_exists($path = $templatePath . '/styles.css'))
		{
			$publicPath = str_replace('\\', '/', App::getInstance()->root . '/public_html');
			$path = str_replace($publicPath, '', $path);
			AssetLoader::getInstance()->addCss($path);
		}

		$isEditMode = $this->isEditMode() && $this->isEditableInAdmin();

		if ($isEditMode) {
			$this->registerEditAssets();
			echo $this->renderEditWrapperOpen();
			echo '<div class="component-edit__content">';
		}

        if (file_exists($viewPath))
        {
            include $viewPath;
        }
        else
        {
            echo "Template `{$template}` of component `{$classPath}` not found<br/>";
        }

		if ($isEditMode) {
			echo '</div>';
			if ($this->isOptimizationMode()) {
				echo $this->renderPerformanceBadge($this->elapsedMilliseconds($startedAt));
			}
			echo $this->renderEditTriggerButton();
			echo $this->renderEditWrapperClose();
		}
    }

	final protected function getClassPath() : string
	{
		$reflection = new \ReflectionClass(static::class);
		$classFile = $reflection->getFileName();
		$classPath = dirname($classFile);

		return $classPath;
	}

	protected function isEditMode(): bool
	{
		return (new EditModeService())->isActive();
	}

	protected function isOptimizationMode(): bool
	{
		return (new EditModeService())->isOptimizationActive();
	}

	protected function isEditableInAdmin(): bool
	{
		return true;
	}

	protected function getEditDataAttributes(): string
	{
		return '';
	}

	protected function getEditDisplayClass(): string
	{
		return '';
	}

	protected function getEditableParamKeys(): array
	{
		$exclude = [
			'items',
			'filters',
			'experience',
			'content',
			'edit_mode',
			'template',
			'error',
			'edit_key',
		];

		$result = [];
		foreach ($this->params as $key => $value) {
			if (in_array($key, $exclude, true)) {
				continue;
			}

			if (is_scalar($value) || $value === null) {
				$result[] = $key;
			}
		}

		return $result;
	}

	protected function getEditableParamsForJs(): array
	{
		$result = [];
		foreach ($this->getEditableParamKeys() as $key) {
			$result[$key] = $this->getParam($key);
		}

		$result['template'] = (string) ($this->getParam('template') ?: 'Default');

		return $result;
	}

	/**
	 * @return list<string>
	 */
	protected function getAvailableTemplates(): array
	{
		$classPath = $this->getClassPath();
		if (!is_dir($classPath)) {
			return ['Default'];
		}

		$templates = [];
		$entries = scandir($classPath);
		if ($entries === false) {
			return ['Default'];
		}

		foreach ($entries as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}

			$templateDir = $classPath . DIRECTORY_SEPARATOR . $entry;
			if (!is_dir($templateDir)) {
				continue;
			}

			if (!is_file($templateDir . DIRECTORY_SEPARATOR . 'index.php')) {
				continue;
			}

			$templates[] = $entry;
		}

		sort($templates, SORT_STRING);

		if ($templates === []) {
			return ['Default'];
		}

		if (in_array('Default', $templates, true)) {
			$templates = array_values(array_diff($templates, ['Default']));
			array_unshift($templates, 'Default');
		}

		return $templates;
	}

	protected function getEditKey(): string
	{
		return $this->editKey;
	}

	protected function getComponentShortName(): string
	{
		$className = (new \ReflectionClass(static::class))->getShortName();

		return $className;
	}

	private function resolveEditKey(array $params): string
	{
		$editKey = trim((string) ($params['edit_key'] ?? ''));
		if ($editKey !== '') {
			return $editKey;
		}

		$page = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
		self::$editInstanceCounter++;

		return $page . '#' . $this->getComponentShortName() . '#' . self::$editInstanceCounter;
	}

	private function mergeStoredParams(array $params): array
	{
		$stored = ComponentSettingsStorage::get($this->editKey);
		if ($stored === []) {
			return $params;
		}

		return array_merge($params, $stored);
	}

	private function registerEditAssets(): void
	{
		if (self::$editAssetsRegistered) {
			return;
		}

		self::$editAssetsRegistered = true;
		AssetLoader::getInstance()->addCss('/Components/ComponentEdit/Default/styles.css');
		AssetLoader::getInstance()->addJs('/Components/ComponentEdit/Default/script.js');
	}

	private function renderEditWrapperOpen(): string
	{
		$displayClass = trim($this->getEditDisplayClass());
		$classList = 'component-edit' . ($displayClass !== '' ? ' ' . $displayClass : '');
		$type = $this->getComponentShortName();
		$editKey = $this->getEditKey();
		$paramsJson = json_encode($this->getEditableParamsForJs(), JSON_UNESCAPED_UNICODE);
		if ($paramsJson === false) {
			$paramsJson = '{}';
		}

		$templatesList = implode(',', $this->getAvailableTemplates());
		if ($templatesList === '') {
			$templatesList = 'Default';
		}

		return sprintf(
			'<div class="%s" data-component-type="%s" data-component-key="%s" data-component-label="%s" data-component-params="%s" data-component-templates="%s"%s>',
			htmlspecialchars($classList),
			htmlspecialchars($type),
			htmlspecialchars($editKey),
			htmlspecialchars($type),
			htmlspecialchars($paramsJson, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
			htmlspecialchars($templatesList, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
			$this->getEditDataAttributes()
		);
	}

	private function renderEditTriggerButton(): string
	{
		return '<button class="component-edit__trigger" type="button" aria-label="Настройки компонента" title="Настройки компонента">'
			. '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
			. '<path d="M12 8.25a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5Zm8.25 3.75a8.18 8.18 0 0 0-.11-1.03l2.02-1.57a.75.75 0 0 0 .18-.96l-1.92-3.32a.75.75 0 0 0-.9-.33l-2.38.96a8.32 8.32 0 0 0-1.78-1.03l-.36-2.54A.75.75 0 0 0 14.7 2h-3.4a.75.75 0 0 0-.74.64l-.36 2.54c-.64.25-1.24.59-1.78 1.03l-2.38-.96a.75.75 0 0 0-.9.33L2.22 8.5a.75.75 0 0 0 .18.96l2.02 1.57c-.07.34-.11.68-.11 1.03s.04.69.11 1.03l-2.02 1.57a.75.75 0 0 0-.18.96l1.92 3.32c.19.33.6.47.9.33l2.38-.96c.54.44 1.14.78 1.78 1.03l.36 2.54c.08.36.38.64.74.64h3.4c.36 0 .66-.28.74-.64l.36-2.54a8.32 8.32 0 0 0 1.78-1.03l2.38.96c.3.12.71-.01.9-.33l1.92-3.32a.75.75 0 0 0-.18-.96l-2.02-1.57c.07-.34.11-.68.11-1.03Z"/>'
			. '</svg></button>';
	}

	private function renderEditWrapperClose(): string
	{
		return '</div>';
	}

	private function renderPerformanceBadge(float $renderTimeMs): string
	{
		$totalTimeMs = $this->preparationTimeMs + $renderTimeMs;

		return sprintf(
			'<div class="component-edit__performance" title="Время выполнения компонента">Подготовка: %s с · Рендер: %s с · Всего: %s с</div>',
			$this->formatSeconds($this->preparationTimeMs),
			$this->formatSeconds($renderTimeMs),
			$this->formatSeconds($totalTimeMs)
		);
	}

	private function elapsedMilliseconds(int $startedAt): float
	{
		return (hrtime(true) - $startedAt) / 1_000_000;
	}

	private function formatSeconds(float $milliseconds): string
	{
		return number_format($milliseconds / 1000, 6, ',', '');
	}
}
