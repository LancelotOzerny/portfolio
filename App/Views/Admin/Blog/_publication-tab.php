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
$isPublished = $publicationService->isPublished($article);
$scheduleInputValue = $publicationService->formatForInput(
	$scheduledDatetime !== null ? $scheduledDatetime : date('Y-m-d H:i:s', strtotime('+1 day'))
);

$publicationHeading = 'Время публикации';
if (!$isPublished && $scheduledDatetime !== null) {
	$scheduledLabel = $dateFormatter->formatWithTime($scheduledDatetime) ?: $scheduledDatetime;
	$publicationHeading .= ' (отложено на: ' . $scheduledLabel . ')';
}
?>

<div class="row g-3">
	<div class="col-12">
		<h2 class="h5 mb-2"><?= htmlspecialchars($publicationHeading) ?></h2>
		<?php if ($isPublished && $publicationDatetime !== null): ?>
			<p class="text-secondary mb-0">
				Опубликована: <?= htmlspecialchars($dateFormatter->formatWithTime($publicationDatetime) ?: $publicationDatetime) ?>
			</p>
		<?php elseif (!$isPublished): ?>
			<p class="text-secondary mb-0">Статья ещё не опубликована.</p>
		<?php endif; ?>
	</div>

	<?php if (!$isPublished): ?>
		<div class="col-12">
			<div class="d-flex flex-wrap gap-2">
				<button
					type="submit"
					form="blog-article-publish-form"
					class="btn btn-success"
					onclick="return confirm('Опубликовать статью сейчас?');"
				>Опубликовать</button>
				<button
					type="button"
					class="btn btn-outline-primary"
					data-bs-toggle="modal"
					data-bs-target="#blog-article-schedule-modal"
				>Отложить публикацию</button>
			</div>
		</div>
	<?php endif; ?>
</div>
