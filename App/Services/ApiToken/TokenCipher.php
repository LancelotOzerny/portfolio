<?php

namespace App\Services\ApiToken;

use Modules\Main\App;
use Modules\Main\Config;
use RuntimeException;

final class TokenCipher
{
	private const CIPHER = 'aes-256-gcm';
	private const CONFIG_FILE = 'token';

	public function encrypt(string $plainToken): string
	{
		$key = $this->key();
		$iv = random_bytes(12);
		$tag = '';
		$encrypted = openssl_encrypt($plainToken, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag);

		if (!is_string($encrypted) || $tag === '') {
			throw new RuntimeException('Не удалось зашифровать токен.');
		}

		return base64_encode($iv . $tag . $encrypted);
	}

	public function decrypt(string $payload): string
	{
		$raw = base64_decode($payload, true);
		if (!is_string($raw) || strlen($raw) < 29) {
			throw new RuntimeException('Не удалось расшифровать токен.');
		}

		$iv = substr($raw, 0, 12);
		$tag = substr($raw, 12, 16);
		$encrypted = substr($raw, 28);
		$plain = openssl_decrypt($encrypted, self::CIPHER, $this->key(), OPENSSL_RAW_DATA, $iv, $tag);

		if (!is_string($plain) || $plain === '') {
			throw new RuntimeException('Не удалось расшифровать токен.');
		}

		return $plain;
	}

	private function key(): string
	{
		return hash('sha256', $this->secret(), true);
	}

	private function secret(): string
	{
		$config = Config::getInstance()->get('Hidden', self::CONFIG_FILE);
		$secret = trim((string) ($config->secret ?? ''));
		if ($secret !== '') {
			return $secret;
		}

		$secret = bin2hex(random_bytes(32));
		$this->writeSecret($secret);
		Config::getInstance()->clear();

		return $secret;
	}

	private function writeSecret(string $secret): void
	{
		$directory = App::getInstance()->root . '/App/Configs/Hidden';
		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
			throw new RuntimeException('Не удалось создать папку скрытых конфигов.');
		}

		$filePath = $directory . '/' . self::CONFIG_FILE . '.php';
		$exported = var_export($secret, true);
		$content = "<?php\nreturn [\n\t'secret' => {$exported},\n];\n";

		if (file_put_contents($filePath, $content, LOCK_EX) === false) {
			throw new RuntimeException('Не удалось сохранить ключ шифрования токенов.');
		}
	}
}
