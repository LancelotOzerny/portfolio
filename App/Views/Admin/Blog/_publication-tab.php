<?php
/* @var array $data */

use App\Services\Blog\BlogArticlePublicationService;
use App\Services\Blog\BlogDateFormatter;

$article = $data['article'] ?? null;
$articleId = (int) ($article->id ?? 0);
$publicationService = new BlogArticlePublicationService();
$dateFormatter = new BlogDateFormatter();
$publicationDatetime = $data['publicationDatetime'] ?? $publicationService->getPublicationDatetime($article);
$scheduledDatetime = $data['scheduledDatetime'] ?? $publicationService->getScheduledDatetime($article);
$scheduleInputValue = $publicationService->formatForInput(
	$scheduledDatetime !== null ? $scheduledDatetime : date('Y-m-d H:i:s', strtotime('+1 hour'))
);
$publicationLabel = $publicationDatetime !== null
	? ($dateFormatter->format($publicationDatetime) ?: $publicationDatetime)
	: 'Нет';
?>

<div class="row g-3">
	<div class="col-12">
		<label class="form-label">Время публикации</label>
		<input type="text" class="form-control" value="<?= htmlspecialchars($publicationLabel) ?>" readonly>
	</div>

	<?php if ($scheduledDatetime !== null): ?>
		<div class="col-12">
			<div class="alert alert-info mb-0">
				Запланирована публикация: <?= htmlspecialchars($dateFormatter->format($scheduledDatetime) ?: $scheduledDatetime) ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="col-12">
		<h2 class="h6 mb-2">Опубликовать потом</h2>
		<form action="/admin/content/blog/articles/<?= $articleId ?>/schedule/" method="post" class="row g-2 align-items-end">
			<div class="col-12 col-md-6">
				<label class="form-label" for="blog-article-schedule-at">Время публикации</label>
				<input
					type="datetime-local"
					class="form-control"
					id="blog-article-schedule-at"
					name="published_at"
					value="<?= htmlspecialchars($scheduleInputValue) ?>"
					required
				>
			</div>
			<div class="col-12 col-md-auto">
				<button type="submit" class="btn btn-outline-primary">Запланировать</button>
			</div>
		</form>
	</div>

	<div class="col-12">
		<form action="/admin/content/blog/articles/<?= $articleId ?>/publish/" method="post" onsubmit="return confirm('Опубликовать статью сейчас?');">
			<button type="submit" class="btn btn-success">Опубликовать</button>
		</form>
	</div>
</div>
