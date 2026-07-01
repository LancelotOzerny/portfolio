<?php
/** @var array $data */

$backups = is_array($data['backups'] ?? null) ? $data['backups'] : [];
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
?>

<section class="admin-backup-list">
	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h1 class="h4 mb-0">Список копий</h1>
				<a href="/admin/settings/backup/create/" class="btn btn-primary btn-sm">Создать копию</a>
			</div>

			<?php if ($flash !== null): ?>
				<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
					<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
				</div>
			<?php endif; ?>

			<form action="/admin/settings/backup/upload/" method="post" enctype="multipart/form-data" class="mb-4">
				<label class="form-label small text-secondary" for="backupFile">Загрузить копию</label>
				<input class="form-control" type="file" id="backupFile" name="backup_file" accept=".zip,application/zip" onchange="this.form.submit()">
			</form>

			<?php if (empty($backups)): ?>
				<div class="alert alert-secondary mb-0">Резервных копий пока нет.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table align-middle">
						<thead>
							<tr>
								<th scope="col">#</th>
								<th scope="col">Название копии</th>
								<th scope="col">Дата и время создания</th>
								<th scope="col">Вес</th>
								<th scope="col">Управление</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($backups as $index => $backup): ?>
								<?php $fileName = (string) ($backup['name'] ?? ''); ?>
								<tr>
									<td><?= $index + 1 ?></td>
									<td class="fw-semibold"><?= htmlspecialchars($fileName) ?></td>
									<td><?= htmlspecialchars((string) ($backup['created_at'] ?? '')) ?></td>
									<td><?= htmlspecialchars((string) ($backup['size'] ?? '')) ?></td>
									<td>
										<div class="d-flex flex-wrap gap-2">
											<form action="/admin/settings/backup/delete/<?= rawurlencode($fileName) ?>/" method="post" onsubmit="return confirm('Удалить резервную копию?');">
												<button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
											</form>

											<form action="/admin/settings/backup/restore/<?= rawurlencode($fileName) ?>/" method="post" class="d-flex gap-2" onsubmit="return confirm('Восстановить проект из выбранной копии?');">
												<select name="mode" class="form-select form-select-sm" aria-label="Режим восстановления">
													<option value="overlay">Наложение</option>
													<option value="exact">Точное восстановление</option>
													<option value="missing">Недостающее</option>
												</select>
												<button type="submit" class="btn btn-outline-primary btn-sm">Восстановить</button>
											</form>

											<a class="btn btn-outline-secondary btn-sm" href="/admin/settings/backup/download/<?= rawurlencode($fileName) ?>/">Скачать копию</a>
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
