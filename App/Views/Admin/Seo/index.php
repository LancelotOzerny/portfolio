<?php
/* @var array $data */

$items = $data['items'] ?? [];
$saved = (bool) ($data['saved'] ?? false);
$reset = (bool) ($data['reset'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
?>

<section class="admin-seo">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<h1 class="h3 mb-0">SEO</h1>
			<div class="text-secondary small">Управление мета-данными страниц сайта</div>
		</div>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">SEO-настройки сохранены.</div>
	<?php endif; ?>
	<?php if ($reset): ?>
		<div class="alert alert-success">Ручные SEO-настройки сброшены.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<div class="card border-0 shadow-sm">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th scope="col">Страница</th>
						<th scope="col">URL</th>
						<th scope="col">Title</th>
						<th scope="col">Статус</th>
						<th scope="col" class="text-end">Действия</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($items as $item): ?>
						<tr>
							<td class="fw-semibold"><?= htmlspecialchars((string) ($item['label'] ?? '')) ?></td>
							<td><code><?= htmlspecialchars((string) ($item['path'] ?? '')) ?></code></td>
							<td><?= htmlspecialchars((string) ($item['title'] ?? '')) ?></td>
							<td>
								<?php
								$status = (string) ($item['status'] ?? '');
								$badgeClass = match ($status) {
									'Настроено' => 'text-bg-success',
									'Запрещена индексация' => 'text-bg-danger',
									'Не заполнено описание' => 'text-bg-warning',
									default => 'text-bg-secondary',
								};
								?>
								<span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
							</td>
							<td class="text-end">
								<a class="btn btn-outline-primary btn-sm" href="/admin/seo/<?= rawurlencode((string) ($item['key'] ?? '')) ?>/">Редактировать</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
