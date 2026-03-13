<?php

namespace Modules\Main;

class ConfigObject
{
	protected array $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function __get(string $key)
	{
		$value = $this->data[$key] ?? null;

		// Если значение — массив, возвращаем ConfigObject
		if (is_array($value)) {
			return new ConfigObject($value);
		}

		// Иначе — скаляр или null (для echo $db->host)
		return $value;
	}

	public function toArray(): array
	{
		return $this->data;
	}

	public function __toString(): string
	{
		return json_encode($this->data, JSON_PRETTY_PRINT);
	}

	// Дополнительно: get() для dot-нотации
	public function get(string $path, $default = null)
	{
		$keys = explode('.', $path);
		$value = $this->data;
		foreach ($keys as $key) {
			if (!isset($value[$key])) {
				return $default;
			}
			$value = $value[$key];
			if (!is_array($value)) {
				break;
			}
		}
		return $value;
	}
}