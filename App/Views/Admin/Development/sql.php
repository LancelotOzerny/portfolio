<?php
/** @var array $data */

$files = is_array($data['files'] ?? null) ? $data['files'] : [];
$selectedFile = (string) ($data['selectedFile'] ?? '');
$selectedSql = (string) ($data['selectedSql'] ?? '');
$loadError = (string) ($data['loadError'] ?? '');
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
$csrfToken = (string) ($data['csrfToken'] ?? '');
$details = is_array($flash['details'] ?? null) ? $flash['details'] : [];
$editorSql = $selectedSql !== '' ? $selectedSql : (string) ($details['sql'] ?? '');
?>

<section class="admin-development-sql">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
		<h1 class="h4 mb-0">SQL запросы</h1>
		<a href="/admin/" class="btn btn-outline-secondary btn-sm">Назад в админку</a>
	</div>

	<?php if ($flash !== null): ?>
		<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
			<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
		</div>
	<?php endif; ?>

	<?php if ($loadError !== ''): ?>
		<div class="alert alert-danger" role="alert">
			<?= htmlspecialchars($loadError) ?>
		</div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h2 class="h5 mb-0">Выполнить SQL</h2>
				<?php if ($selectedFile !== ''): ?>
					<span class="badge text-bg-light border">Загружен файл: <?= htmlspecialchars($selectedFile) ?></span>
				<?php endif; ?>
			</div>

			<form action="/admin/development/sql/execute/" method="post">
				<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
				<textarea class="form-control font-monospace" name="sql" rows="14" spellcheck="false" placeholder="SELECT * FROM table_name;"><?= htmlspecialchars($editorSql) ?></textarea>

				<div class="d-flex flex-wrap align-items-center gap-2 mt-3">
					<button type="submit" class="btn btn-primary">Выполнить SQL</button>
					<a class="btn btn-outline-secondary" href="/admin/development/sql/">Очистить</a>
				</div>
			</form>

			<?php if (!empty($details)): ?>
				<hr>
				<?php if (($details['type'] ?? '') === 'rows'): ?>
					<?php $rows = is_array($details['rows'] ?? null) ? $details['rows'] : []; ?>
					<h3 class="h6 mb-3">Результат выборки: <?= count($rows) ?></h3>
					<?php if (empty($rows)): ?>
						<div class="alert alert-secondary mb-0">Запрос не вернул строки.</div>
					<?php else: ?>
						<div class="table-responsive">
							<table class="table table-sm table-bordered align-middle mb-0">
								<thead>
									<tr>
										<?php foreach (array_keys($rows[0]) as $column): ?>
											<th><?= htmlspecialchars((string) $column) ?></th>
										<?php endforeach; ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($rows as $row): ?>
										<tr>
											<?php foreach ($row as $value): ?>
												<td><?= htmlspecialchars((string) $value) ?></td>
											<?php endforeach; ?>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
				<?php elseif (($details['type'] ?? '') === 'affected'): ?>
					<div class="alert alert-light border mb-0">
						Затронуто строк: <?= htmlspecialchars((string) ($details['affectedRows'] ?? '0')) ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<h2 class="h5 mb-3">SQL файлы миграций</h2>

			<?php if (empty($files)): ?>
				<div class="alert alert-secondary mb-0">В папке storage/migrations нет SQL файлов.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table align-middle mb-0">
						<thead>
							<tr>
								<th>Файл</th>
								<th>Размер</th>
								<th>Изменен</th>
								<th class="text-end">Действия</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($files as $file): ?>
								<?php $fileName = (string) ($file['name'] ?? ''); ?>
								<tr>
									<td class="font-monospace"><?= htmlspecialchars($fileName) ?></td>
									<td><?= number_format((int) ($file['size'] ?? 0), 0, '.', ' ') ?> байт</td>
									<td><?= htmlspecialchars((string) ($file['updated_at'] ?? '')) ?></td>
									<td>
										<div class="d-flex flex-wrap justify-content-end gap-2">
											<a class="btn btn-outline-secondary btn-sm" href="/admin/development/sql/?file=<?= rawurlencode($fileName) ?>">
												Редактировать
											</a>

											<form action="/admin/development/sql/execute-file/" method="post" class="mb-0" onsubmit="return confirm('Выполнить SQL файл «<?= htmlspecialchars($fileName, ENT_QUOTES) ?>»?');">
												<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
												<input type="hidden" name="file" value="<?= htmlspecialchars($fileName) ?>">
												<button type="submit" class="btn btn-outline-primary btn-sm">Выполнить</button>
											</form>

											<form action="/admin/development/sql/delete-file/" method="post" class="mb-0" onsubmit="return confirm('Удалить SQL файл «<?= htmlspecialchars($fileName, ENT_QUOTES) ?>»?');">
												<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
												<input type="hidden" name="file" value="<?= htmlspecialchars($fileName) ?>">
												<button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
											</form>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
