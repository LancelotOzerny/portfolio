<?php
/* @var array $data */

$currentProject = $data['info'] ?? null;
if ($currentProject === null) {
    ?>
    <div class="container detail-not-found">
        <div class="alert alert-warning mb-0">Проект не найден.</div>
    </div>
    <?php
    return;
}

$projectsGrid = new \Components\ProjectsGrid\ProjectsGrid([
    'use_filters' => false,
    'limit' => 3,
    'random' => true,
    'exclude_id' => (int) $currentProject->id
]);

$projectInfoItems = array_values(array_filter(
    is_array($currentProject->info ?? null) ? $currentProject->info : [],
    static function ($item): bool {
        if (!is_object($item)) {
            return false;
        }

        return trim((string) ($item->date ?? '')) !== ''
            || trim((string) ($item->develop_time ?? '')) !== ''
            || trim((string) ($item->version ?? '')) !== '';
    }
));

$formatDate = static function (string $rawDate): string {
    $rawDate = trim($rawDate);
    if ($rawDate === '') {
        return '';
    }

    try {
        return (new DateTime($rawDate))->format('d/m/Y');
    } catch (Exception) {
        return $rawDate;
    }
};
?>

<section class="detail-hero">
    <div class="container detail-hero__container">
        <a class="detail-hero__back" href="/portfolio/">← К портфолио</a>
        <h1 class="detail-hero__title"><?= htmlspecialchars((string) $currentProject->name) ?></h1>

        <?php if (trim((string) $currentProject->preview_text) !== ''): ?>
            <div class="detail-hero__lead">
                <?= $currentProject->preview_text ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($currentProject->tags)): ?>
            <div class="detail-tags">
                <?php foreach ($currentProject->tags as $tag): ?>
                    <span class="detail-tag"><?= htmlspecialchars((string) ($tag->name ?? '')) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<main class="container detail-layout scroll-show-area">
    <article class="detail-content">
        <?php if (!empty($currentProject->detail_image_url)): ?>
            <figure class="detail-image">
                <img src="<?= htmlspecialchars((string) $currentProject->detail_image_url) ?>"
                     alt="<?= htmlspecialchars((string) $currentProject->name) ?>"
                     title="<?= htmlspecialchars((string) $currentProject->name) ?>">
            </figure>
        <?php endif; ?>

        <div class="detail-text">
            <?= $currentProject->detail_text ?>
        </div>
    </article>

    <aside class="detail-sidebar">
        <div class="detail-panel">
            <h2 class="detail-panel__title">Детали проекта</h2>

            <?php if (!empty($projectInfoItems)): ?>
                <?php foreach ($projectInfoItems as $infoIndex => $infoItem): ?>
                    <?php
                    $infoDate = $formatDate((string) ($infoItem->date ?? ''));
                    $infoDevelopTime = trim((string) ($infoItem->develop_time ?? ''));
                    $infoVersion = trim((string) ($infoItem->version ?? ''));
                    ?>

                    <dl class="detail-meta">
                        <?php if ($infoDate !== ''): ?>
                            <div class="detail-meta__item">
                                <dt>Дата</dt>
                                <dd><?= htmlspecialchars($infoDate) ?></dd>
                            </div>
                        <?php endif; ?>

                        <?php if ($infoDevelopTime !== ''): ?>
                            <div class="detail-meta__item">
                                <dt>Время разработки</dt>
                                <dd><?= htmlspecialchars($infoDevelopTime) ?></dd>
                            </div>
                        <?php endif; ?>

                        <?php if ($infoVersion !== ''): ?>
                            <div class="detail-meta__item">
                                <dt>Версия</dt>
                                <dd><?= htmlspecialchars($infoVersion) ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>

                    <?php if ($infoIndex < count($projectInfoItems) - 1): ?>
                        <hr class="detail-panel__divider">
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <dl class="detail-meta">
                    <div class="detail-meta__item">
                        <dt>Дата</dt>
                        <dd><?= htmlspecialchars($formatDate((string) ($currentProject->created_at ?? ''))) ?></dd>
                    </div>
                </dl>
            <?php endif; ?>

            <?php if (!empty($currentProject->links)): ?>
                <h2 class="detail-panel__title detail-panel__title_links">Ссылки</h2>
                <div class="detail-links">
                    <?php foreach ($currentProject->links as $link): ?>
                        <a href="<?= htmlspecialchars((string) ($link->link ?? '')) ?>" target="_blank" rel="noopener">
                            <?= htmlspecialchars((string) ($link->name ?? '')) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </aside>
</main>

<section class="container detail-related scroll-show-area">
    <div class="detail-related__title">
        <h2>Похожие проекты</h2>
    </div>

    <div class="detail-related__content">
        <?php $projectsGrid->render(); ?>
    </div>
</section>
