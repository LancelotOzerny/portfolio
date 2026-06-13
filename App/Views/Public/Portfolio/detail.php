<?php
/* @var array $data */

$currentProject = $data['info'] ?? null;
if ($currentProject === null) {
    ?>
    <div class="container detail-not-found">
        <div class="alert alert-warning mb-0">&#1055;&#1088;&#1086;&#1077;&#1082;&#1090; &#1085;&#1077; &#1085;&#1072;&#1081;&#1076;&#1077;&#1085;.</div>
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
        <a class="detail-hero__back" href="/portfolio/">&#8592; &#1050; &#1087;&#1086;&#1088;&#1090;&#1092;&#1086;&#1083;&#1080;&#1086;</a>
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
            <h2 class="detail-panel__title">&#1044;&#1077;&#1090;&#1072;&#1083;&#1080; &#1087;&#1088;&#1086;&#1077;&#1082;&#1090;&#1072;</h2>

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
                                <dt>&#1044;&#1072;&#1090;&#1072;</dt>
                                <dd><?= htmlspecialchars($infoDate) ?></dd>
                            </div>
                        <?php endif; ?>

                        <?php if ($infoDevelopTime !== ''): ?>
                            <div class="detail-meta__item">
                                <dt>&#1042;&#1088;&#1077;&#1084;&#1103; &#1088;&#1072;&#1079;&#1088;&#1072;&#1073;&#1086;&#1090;&#1082;&#1080;</dt>
                                <dd><?= htmlspecialchars($infoDevelopTime) ?></dd>
                            </div>
                        <?php endif; ?>

                        <?php if ($infoVersion !== ''): ?>
                            <div class="detail-meta__item">
                                <dt>&#1042;&#1077;&#1088;&#1089;&#1080;&#1103;</dt>
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
                        <dt>&#1044;&#1072;&#1090;&#1072;</dt>
                        <dd><?= htmlspecialchars($formatDate((string) ($currentProject->created_at ?? ''))) ?></dd>
                    </div>
                </dl>
            <?php endif; ?>

            <?php if (!empty($currentProject->links)): ?>
                <h2 class="detail-panel__title detail-panel__title_links">&#1057;&#1089;&#1099;&#1083;&#1082;&#1080;</h2>
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
        <h2>&#1055;&#1086;&#1093;&#1086;&#1078;&#1080;&#1077; &#1087;&#1088;&#1086;&#1077;&#1082;&#1090;&#1099;</h2>
        <p>&#1057;&#1083;&#1091;&#1095;&#1072;&#1081;&#1085;&#1072;&#1103; &#1087;&#1086;&#1076;&#1073;&#1086;&#1088;&#1082;&#1072; &#1088;&#1072;&#1073;&#1086;&#1090; &#1073;&#1077;&#1079; &#1090;&#1077;&#1082;&#1091;&#1097;&#1077;&#1075;&#1086; &#1087;&#1088;&#1086;&#1077;&#1082;&#1090;&#1072;</p>
    </div>

    <div class="detail-related__content">
        <?php $projectsGrid->render(); ?>
    </div>
</section>
