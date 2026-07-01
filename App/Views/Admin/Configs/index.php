<?php
/** @var array $data */

$catalog = is_array($data['catalog'] ?? null) ? $data['catalog'] : [];
$selectedSection = (string) ($data['selectedSection'] ?? '');
$selectedFile = (string) ($data['selectedFile'] ?? '');
$configValues = $data['configValues'] ?? null;
$loadError = (string) ($data['loadError'] ?? '');
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;

$buildEditorUrl = static function (string $section, string $file): string {
	$query = http_build_query([
		'section' => $section,
		'file' => $file,
	]);

	return '/admin/settings/configs/' . ($query !== '' ? ('?' . $query) : '');
};

$exportPhpValue = null;
$exportPhpValue = static function (mixed $value, int $level = 0) use (&$exportPhpValue): string {
	if (is_array($value)) {
		if ($value === []) {
			return '[]';
		}

		$indent = str_repeat("\t", $level);
		$childIndent = str_repeat("\t", $level + 1);
		$lines = ['['];

		foreach ($value as $key => $item) {
			$keyLiteral = is_int($key) ? (string) $key : var_export((string) $key, true);
			$lines[] = $childIndent . $keyLiteral . ' => ' . $exportPhpValue($item, $level + 1) . ',';
		}

		$lines[] = $indent . ']';

		return implode(PHP_EOL, $lines);
	}

	if (is_string($value)) {
		return var_export($value, true);
	}

	if (is_bool($value)) {
		return $value ? 'true' : 'false';
	}

	if ($value === null) {
		return 'null';
	}

	if (is_float($value) || is_int($value)) {
		return (string) $value;
	}

	return var_export($value, true);
};
?>

<section class="admin-configs">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Конфиги</h1>
		<a href="/admin/settings/" class="btn btn-outline-secondary btn-sm">Назад в настройки</a>
	</div>

	<?php if ($flash !== null): ?>
		<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
			<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
		</div>
	<?php endif; ?>

	<div class="row g-3">
		<div class="col-lg-4">
			<div class="card border-0 shadow-sm h-100">
				<div class="card-body">
					<h2 class="h6 mb-3">Список конфигураций</h2>

					<?php if (empty($catalog)): ?>
						<div class="alert alert-secondary mb-0">Папка конфигураций пуста.</div>
					<?php else: ?>
						<?php foreach ($catalog as $section => $files): ?>
							<div class="mb-3">
								<div class="fw-semibold mb-2"><?= htmlspecialchars((string) $section) ?></div>
								<?php if (empty($files)): ?>
									<div class="small text-muted">Нет файлов</div>
								<?php else: ?>
									<div class="list-group list-group-flush border rounded-2">
										<?php foreach ($files as $file): ?>
											<?php
											$isActive = $selectedSection === $section && $selectedFile === $file;
											?>
											<a href="<?= htmlspecialchars($buildEditorUrl((string) $section, (string) $file)) ?>"
											   class="list-group-item list-group-item-action py-2<?= $isActive ? ' active' : '' ?>">
												<?= htmlspecialchars((string) $file) ?>.php
											</a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="col-lg-8">
			<div class="card border-0 shadow-sm">
				<div class="card-body">
					<?php if ($loadError !== ''): ?>
						<div class="alert alert-danger mb-0"><?= htmlspecialchars($loadError) ?></div>
					<?php elseif ($selectedSection === '' || $selectedFile === ''): ?>
						<div class="alert alert-secondary mb-0">Выберите конфигурационный файл слева.</div>
					<?php elseif (!is_array($configValues)): ?>
						<div class="alert alert-warning mb-0">Не удалось загрузить содержимое файла.</div>
					<?php else: ?>
						<div class="d-flex justify-content-between align-items-center mb-3">
							<h2 class="h6 mb-0">Редактирование: <?= htmlspecialchars($selectedSection . '/' . $selectedFile . '.php') ?></h2>
						</div>

						<form action="/admin/settings/configs/save/" method="post">
							<input type="hidden" name="section" value="<?= htmlspecialchars($selectedSection) ?>">
							<input type="hidden" name="file" value="<?= htmlspecialchars($selectedFile) ?>">

							<div class="alert alert-light border small">
								Редактируйте PHP-массив целиком. Можно добавлять, удалять и переставлять элементы; перед сохранением структура будет проверена.
							</div>

							<textarea class="form-control font-monospace" name="config_content" rows="22" spellcheck="false"><?= htmlspecialchars($exportPhpValue($configValues)) ?></textarea>

							<div class="mt-3">
								<button type="submit" class="btn btn-primary">Сохранить изменения</button>
							</div>
						</form>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
