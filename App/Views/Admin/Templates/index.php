<?php
/** @var array $data */

$templates = is_array($data['templates'] ?? null) ? $data['templates'] : [];
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
?>

<section class="admin-templates">
	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h1 class="h4 mb-0">Шаблоны</h1>
				<a href="/admin/settings/templates/create/" class="btn btn-primary btn-sm">Создать шаблон</a>
			</div>

			<?php if ($flash !== null): ?>
				<div class="alert <?= !empty($flash['success']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
					<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
				</div>
			<?php endif; ?>

			<?php if (empty($templates)): ?>
				<div class="alert alert-secondary mb-0">Шаблоны пока не найдены.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table align-middle">
						<thead>
							<tr>
								<th scope="col">Логотип</th>
								<th scope="col">Код</th>
								<th scope="col">Название</th>
								<th scope="col">Описание</th>
								<th scope="col">Статус</th>
								<th scope="col">Управление</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($templates as $template): ?>
								<?php
								$code = (string) ($template['code'] ?? '');
								$logo = (string) ($template['logo'] ?? '');
								$canEdit = !empty($template['can_edit']);
								$canDelete = !empty($template['can_delete']);
								?>
								<tr>
									<td style="width: 96px;">
										<?php if ($logo !== ''): ?>
											<img src="<?= htmlspecialchars($logo) ?>" alt="" class="rounded border" style="width: 72px; height: 52px; object-fit: contain;" loading="lazy" decoding="async">
										<?php else: ?>
											<div class="d-inline-flex align-items-center justify-content-center border rounded text-secondary small" style="width: 72px; height: 52px;">Нет</div>
										<?php endif; ?>
									</td>
									<td class="fw-semibold"><?= htmlspecialchars($code) ?></td>
									<td><?= htmlspecialchars((string) ($template['name'] ?? '')) ?></td>
									<td><?= nl2br(htmlspecialchars((string) ($template['description'] ?? ''))) ?></td>
									<td>
										<?php if (!empty($template['is_active'])): ?>
											<span class="badge text-bg-primary">Используется</span>
										<?php elseif (!empty($template['is_system'])): ?>
											<span class="badge text-bg-secondary">Системный</span>
										<?php else: ?>
											<span class="badge text-bg-light text-secondary border">Свободный</span>
										<?php endif; ?>
									</td>
									<td>
										<div class="d-flex flex-wrap gap-2">
										<?php if ($canEdit): ?>
											<a href="/admin/settings/templates/<?= rawurlencode($code) ?>/" class="btn btn-outline-primary btn-sm">Редактировать</a>
										<?php else: ?>
											<button type="button" class="btn btn-outline-secondary btn-sm" disabled>Редактировать</button>
										<?php endif; ?>

										<?php if ($canDelete): ?>
											<form action="/admin/settings/templates/delete/<?= rawurlencode($code) ?>/" method="post" onsubmit="return confirm('Удалить шаблон?');">
												<button type="submit" class="btn btn-outline-danger btn-sm">Удалить</button>
											</form>
										<?php else: ?>
											<button type="button" class="btn btn-outline-secondary btn-sm" disabled>Удалить</button>
										<?php endif; ?>
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
