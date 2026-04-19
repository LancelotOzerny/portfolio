<?php
/** @var array $data */

$catalog = is_array($data['catalog'] ?? null) ? $data['catalog'] : [];
$selectedSection = (string) ($data['selectedSection'] ?? '');
$selectedFile = (string) ($data['selectedFile'] ?? '');
$configValues = $data['configValues'] ?? null;
$loadError = (string) ($data['loadError'] ?? '');
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
$emptyArrayMarker = '__EMPTY_ARRAY__';

$buildEditorUrl = static function (string $section, string $file): string {
	$query = http_build_query([
		'section' => $section,
		'file' => $file,
	]);

	return '/admin/configs/' . ($query !== '' ? ('?' . $query) : '');
};

$detectType = static function (mixed $value): string {
	return match (true) {
		is_bool($value) => 'bool',
		is_int($value) => 'int',
		is_float($value) => 'float',
		$value === null => 'string',
		default => 'string',
	};
};

$buildInputName = static function (string $root, array $segments): string {
	$name = $root;
	foreach ($segments as $segment) {
		$name .= '[' . (string) $segment . ']';
	}

	return $name;
};

$renderFields = null;
$renderFields = static function (mixed $value, array $segments = [], string $pathLabel = 'root') use (&$renderFields, $detectType, $buildInputName, $emptyArrayMarker): void {
	if (is_array($value)) {
		if ($value === []) {
			$valueName = $buildInputName('values', $segments);
			$typeName = $buildInputName('types', $segments);
			?>
			<input type="hidden" name="<?= htmlspecialchars($valueName) ?>" value="<?= htmlspecialchars($emptyArrayMarker) ?>">
			<input type="hidden" name="<?= htmlspecialchars($typeName) ?>" value="array">
			<div class="alert alert-light border py-2 px-3 mb-2 small">
				Пустой массив: <strong><?= htmlspecialchars($pathLabel) ?></strong>
			</div>
			<?php
		}

		foreach ($value as $key => $item) {
			$nextSegments = [...$segments, $key];
			$nextPath = $pathLabel === 'root'
				? (string) $key
				: $pathLabel . ' > ' . (string) $key;
			$renderFields($item, $nextSegments, $nextPath);
		}

		return;
	}

	$type = $detectType($value);
	$valueName = $buildInputName('values', $segments);
	$typeName = $buildInputName('types', $segments);
	$stringValue = $value === null ? '' : (string) $value;
	$isTextarea = $type === 'string' && (str_contains($stringValue, "\n") || strlen($stringValue) > 90);
	?>

	<div class="mb-3 pb-3 border-bottom">
		<div class="d-flex justify-content-between align-items-center gap-2 mb-2">
			<label class="form-label fw-semibold mb-0"><?= htmlspecialchars($pathLabel) ?></label>
			<span class="badge text-bg-light border"><?= htmlspecialchars($type) ?></span>
		</div>

		<input type="hidden" name="<?= htmlspecialchars($typeName) ?>" value="<?= htmlspecialchars($type) ?>">

		<?php if ($type === 'bool'): ?>
			<select class="form-select form-select-sm" name="<?= htmlspecialchars($valueName) ?>">
				<option value="1" <?= $value ? 'selected' : '' ?>>true</option>
				<option value="0" <?= !$value ? 'selected' : '' ?>>false</option>
			</select>
		<?php elseif ($type === 'int'): ?>
			<input type="number" class="form-control form-control-sm" name="<?= htmlspecialchars($valueName) ?>" value="<?= htmlspecialchars($stringValue) ?>" step="1">
		<?php elseif ($type === 'float'): ?>
			<input type="number" class="form-control form-control-sm" name="<?= htmlspecialchars($valueName) ?>" value="<?= htmlspecialchars($stringValue) ?>" step="any">
		<?php elseif ($isTextarea): ?>
			<textarea class="form-control form-control-sm" name="<?= htmlspecialchars($valueName) ?>" rows="4"><?= htmlspecialchars($stringValue) ?></textarea>
		<?php else: ?>
			<input type="text" class="form-control form-control-sm" name="<?= htmlspecialchars($valueName) ?>" value="<?= htmlspecialchars($stringValue) ?>">
		<?php endif; ?>
	</div>
	<?php
};
?>

<section class="admin-configs">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h1 class="h4 mb-0">Конфиги</h1>
		<a href="/admin/" class="btn btn-outline-secondary btn-sm">Назад в админку</a>
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

						<form action="/admin/configs/save/" method="post">
							<input type="hidden" name="section" value="<?= htmlspecialchars($selectedSection) ?>">
							<input type="hidden" name="file" value="<?= htmlspecialchars($selectedFile) ?>">

							<?php if ($configValues === []): ?>
								<div class="alert alert-light border">Файл содержит пустой массив.</div>
							<?php else: ?>
								<?php $renderFields($configValues); ?>
							<?php endif; ?>

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
