<?php
/** @var array $data */

$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
?>

<section class="admin-backup-create">
	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h1 class="h4 mb-0">Создание копии</h1>
				<a href="/admin/settings/" class="btn btn-outline-secondary btn-sm">Назад в настройки</a>
			</div>

			<?php if ($flash !== null): ?>
				<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
					<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
				</div>
			<?php endif; ?>

			<form action="/admin/settings/backup/create/" method="post" data-backup-form>
				<div class="form-check form-switch mb-3">
					<input class="form-check-input" type="checkbox" value="1" id="includeDatabase" name="include_database">
					<label class="form-check-label" for="includeDatabase">Включить БД</label>
				</div>

				<div class="mb-3">
					<label class="form-label" for="excludedPaths">Исключить файлы и папки из копии</label>
					<textarea class="form-control" id="excludedPaths" name="excluded_paths" rows="6" placeholder="public_html/upload/tmp&#10;storage/cache"></textarea>
					<div class="form-text">Каждый путь укажите с новой строки относительно корня проекта.</div>
				</div>

				<div class="progress mb-3 d-none" data-backup-progress style="height: 18px;">
					<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 10%">Создание резервной копии</div>
				</div>

				<button type="submit" class="btn btn-primary">Создать</button>
			</form>
		</div>
	</div>
</section>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		var form = document.querySelector('[data-backup-form]');
		var progress = document.querySelector('[data-backup-progress]');
		if (!form || !progress) {
			return;
		}

		form.addEventListener('submit', function () {
			progress.classList.remove('d-none');
			var bar = progress.querySelector('.progress-bar');
			var value = 10;
			var timer = setInterval(function () {
				value = Math.min(value + 8, 92);
				bar.style.width = value + '%';
				if (value >= 92) {
					clearInterval(timer);
				}
			}, 350);
		});
	});
</script>
