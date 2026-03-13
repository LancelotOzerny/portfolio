<?php
namespace Modules\Main;

class ViewData
{
	protected static ?self $instance = null;
	protected array $data = [];
	protected array $placeholders = [];

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}

	public function set(string $key, $value): self
	{
		$this->data[$key] = $value;

		if (!isset($this->placeholders[$key])) {
			$this->placeholders[$key] = $this->generatePlaceholder($key);
		}

		return $this;
	}


	public function showParam(string $key): string
	{
		if (!isset($this->placeholders[$key]))
		{
			$placeholder = $this->generatePlaceholder($key);
			$this->placeholders[$key] = $placeholder;
		}
		return $this->placeholders[$key];
	}

	public function replacePlaceholders(string $html): string
	{
		foreach ($this->placeholders as $key => $placeholder)
		{
			$value = $this->data[$key] ?? '';
			$html = str_replace($placeholder, $value, $html);
		}
		return $html;
	}

	protected function generatePlaceholder(string $key): string
	{
		return '[VD_' . bin2hex(random_bytes(8)) . '_' . $key . ']';
	}
}
