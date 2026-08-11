<?php
/* @var array $data */

$comments = is_array($data['comments'] ?? null) ? $data['comments'] : [];
$error = trim((string) ($data['error'] ?? ''));
?>

<section class="admin-blog-comments">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Контент / Блог</div>
			<h1 class="h3 mb-1">Комментарии</h1>
			<p class="text-secondary mb-0">Список комментариев к статьям блога.</p>
		</div>
	</div>

	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if ($comments === []): ?>
		<div class="alert alert-light border">Комментариев пока нет.</div>
	<?php else: ?>
		<div class="card border-0 shadow-sm">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th scope="col">ID</th>
							<th scope="col">Кто оставил</th>
							<th scope="col">Комментарий</th>
							<th scope="col">Статья</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($comments as $comment): ?>
							<?php
							$commentId = (int) ($comment['id'] ?? 0);
							$articleId = (int) ($comment['article_id'] ?? 0);
							$articleUrl = trim((string) ($comment['article_url'] ?? ''));
							?>
							<tr>
								<td class="text-secondary"><?= $commentId ?></td>
								<td class="fw-semibold"><?= htmlspecialchars((string) ($comment['author'] ?? 'Аноним')) ?></td>
								<td style="max-width: 42rem; white-space: pre-wrap; overflow-wrap: anywhere;">
									<?= htmlspecialchars((string) ($comment['text'] ?? '')) ?>
								</td>
								<td>
									<?php if ($articleId > 0 && $articleUrl !== ''): ?>
										<a href="<?= htmlspecialchars($articleUrl) ?>" target="_blank" rel="noopener noreferrer"><?= $articleId ?></a>
									<?php elseif ($articleId > 0): ?>
										<?= $articleId ?>
									<?php else: ?>
										<span class="text-secondary">—</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>
</section>
