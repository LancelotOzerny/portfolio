<?php
namespace Modules\Main;

class Template
{
	protected static ?self $instance = null;
	protected string $template = 'Default';
	public readonly string $templateFolderPath;
	public array $params = [];
	protected array $rules = [];
	protected $localPath = '';

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct()
	{
		$this->templateFolderPath = App::getInstance()->root . '/public_html/Templates';
		$this->loadRules();
		$this->findTemplate();

		$this->localPath = str_replace(App::getInstance()->root . '/public_html', '', $this->templateFolderPath . "/$this->template");
	}

	protected function loadRules(): void
	{
		$config = Config::getInstance()->get('App', 'templates');
		$this->rules = $config->toArray();
	}

	protected function findTemplate(): void
	{
		$uri = App::getInstance()->page ?? ($_SERVER['REQUEST_URI'] ?? '/');
		$path = parse_url($uri, PHP_URL_PATH) ?? '/';

		foreach ($this->rules as $rule) {
			if ($this->matchRule($path, $rule)) {
				$this->template = $rule['template'] ?? 'Default';
				return;
			}
		}

		$this->template = 'Default';
	}

	protected function matchRule(string $path, array $rule): bool
	{
		$method = $rule['method'] ?? 'equal';
		$pattern = $rule['path'] ?? '';

		return match ($method) {
			'equal'  => $path === $pattern,
			'prefix' => $pattern !== '' && str_starts_with($path, rtrim($pattern, '/')),
			'regex'  => $pattern !== '' && preg_match('{'.$pattern.'}', $path),
			default  => false,
		};
	}

	public function setParam(string $key, $value): self
	{
		$this->params[$key] = $value;
		return $this;
	}

	public function getParam(string $key)
	{
		return $this->params[$key] ?? null;
	}

	public function getTemplate(): string
	{
		return $this->template;
	}

	public function showHeader(): void
	{
		$filePath = "$this->templateFolderPath/$this->template/header.php";
		$template_path = "$this->templateFolderPath/$this->template";
		
		if (!file_exists($filePath))
		{
			echo "Header of template '$this->template' is not founded!";
			return;
		}

		extract($this->params, EXTR_SKIP);
		include $filePath;
	}

	public function showFooter(): void
	{
		$filePath = "$this->templateFolderPath/$this->template/footer.php";

		if (!file_exists($filePath)) {
			echo "Footer of template '$this->template' is not founded!";
			return;
		}

		extract($this->params, EXTR_SKIP);
		include $filePath;
	}
}
