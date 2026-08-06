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

$iconSvg = static function (string $path): string {
	return '<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false" fill="currentColor">'
		. $path
		. '</svg>';
};

$iconEdit = $iconSvg('<path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>');
$iconPlay = $iconSvg('<path d="M11.596 8.697l-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z"/>');
$iconDownload = $iconSvg('<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>');
$iconDelete = $iconSvg('<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>');
$iconUpload = $iconSvg('<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>');
?>

<section class="admin-development-sql">
	<style>
		.admin-sql-icon-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 2rem;
			height: 2rem;
			padding: 0;
			line-height: 1;
		}

		.admin-sql-icon-btn svg {
			display: block;
		}
	</style>

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
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h2 class="h5 mb-0">SQL файлы миграций</h2>

				<form
					action="/admin/development/sql/upload/"
					method="post"
					enctype="multipart/form-data"
					class="mb-0"
					id="admin-sql-upload-form"
				>
					<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
					<input
						type="file"
						id="admin-sql-upload-input"
						name="sql_file"
						accept=".sql,text/plain,application/sql"
						class="d-none"
					>
					<button type="button" class="btn btn-primary btn-sm" id="admin-sql-upload-button">
						<span class="d-inline-flex align-items-center gap-2">
							<?= $iconUpload ?>
							<span>Загрузить</span>
						</span>
					</button>
				</form>
			</div>

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
											<a
												class="btn btn-outline-secondary btn-sm admin-sql-icon-btn"
												href="/admin/development/sql/?file=<?= rawurlencode($fileName) ?>"
												title="Редактировать"
												aria-label="Редактировать"
											>
												<?= $iconEdit ?>
											</a>

											<form
												action="/admin/development/sql/execute-file/"
												method="post"
												class="mb-0"
												onsubmit="return confirm('Выполнить SQL файл «<?= htmlspecialchars($fileName, ENT_QUOTES) ?>»?');"
											>
												<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
												<input type="hidden" name="file" value="<?= htmlspecialchars($fileName) ?>">
												<button
													type="submit"
													class="btn btn-outline-primary btn-sm admin-sql-icon-btn"
													title="Выполнить"
													aria-label="Выполнить"
												>
													<?= $iconPlay ?>
												</button>
											</form>

											<a
												class="btn btn-outline-secondary btn-sm admin-sql-icon-btn"
												href="/admin/development/sql/download/<?= rawurlencode($fileName) ?>/"
												title="Скачать"
												aria-label="Скачать"
											>
												<?= $iconDownload ?>
											</a>

											<form
												action="/admin/development/sql/delete-file/"
												method="post"
												class="mb-0"
												onsubmit="return confirm('Удалить SQL файл «<?= htmlspecialchars($fileName, ENT_QUOTES) ?>»?');"
											>
												<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
												<input type="hidden" name="file" value="<?= htmlspecialchars($fileName) ?>">
												<button
													type="submit"
													class="btn btn-outline-danger btn-sm admin-sql-icon-btn"
													title="Удалить"
													aria-label="Удалить"
												>
													<?= $iconDelete ?>
												</button>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
	const uploadButton = document.getElementById('admin-sql-upload-button');
	const uploadInput = document.getElementById('admin-sql-upload-input');
	const uploadForm = document.getElementById('admin-sql-upload-form');

	if (!uploadButton || !uploadInput || !uploadForm) {
		return;
	}

	uploadButton.addEventListener('click', () => {
		uploadInput.click();
	});

	uploadInput.addEventListener('change', () => {
		if (!uploadInput.files || uploadInput.files.length === 0) {
			return;
		}

		uploadForm.submit();
	});
});
</script>
