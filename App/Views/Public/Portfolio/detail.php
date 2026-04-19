<?php
/* @var array $data */

$projectsGrid = new \Components\ProjectsGrid\ProjectsGrid([
    'use_filters' => false,
    'limit' => 3
]);

$currentProject = $data['info'] ?? null;
if ($currentProject === null) {
    ?>
    <div class="container py-5">
        <div class="alert alert-warning mb-0">Проект не найден.</div>
    </div>
    <?php
    return;
}

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

<div class="container pt-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold mb-4"><?= htmlspecialchars((string) $currentProject->name) ?></h1>

            <p class="fs-5 lh-lg">
                <?= $currentProject->preview_text ?>
            </p>

            <?php if (!empty($currentProject->tags)): ?>
                <div class="d-flex gap-3 mb-5 flex-wrap">
                    <?php foreach ($currentProject->tags as $tag): ?>
                        <span class="badge bg-primary"><?= htmlspecialchars((string) ($tag->name ?? '')) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">

        <div class="col-lg-8">
            <?php if (!empty($currentProject->detail_image_url)): ?>
                <img src="<?= htmlspecialchars((string) $currentProject->detail_image_url) ?>"
                     alt="<?= htmlspecialchars((string) $currentProject->name) ?>"
                     title="<?= htmlspecialchars((string) $currentProject->name) ?>"
                     style="width: 100%;">
            <?php endif; ?>

            <?= $currentProject->detail_text ?>
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <div class="card border-0 shadow hover-lift sticky-top" style="top: 96px;">
                <div class="card-body">
                    <h4 class="card-title h5 mb-3">Детали проекта</h4>

                    <?php if (!empty($projectInfoItems)): ?>
                        <?php foreach ($projectInfoItems as $infoIndex => $infoItem): ?>
                            <?php
                            $infoDate = $formatDate((string) ($infoItem->date ?? ''));
                            $infoDevelopTime = trim((string) ($infoItem->develop_time ?? ''));
                            $infoVersion = trim((string) ($infoItem->version ?? ''));
                            ?>

                            <ul class="list-unstyled mb-4">
                                <?php if ($infoDate !== ''): ?>
                                    <li class="mb-3">
                                        <i class="bi bi-calendar-event text-muted me-2"></i>
                                        <strong>Дата:</strong> <?= htmlspecialchars($infoDate) ?>
                                    </li>
                                <?php endif; ?>

                                <?php if ($infoDevelopTime !== ''): ?>
                                    <li class="mb-3">
                                        <i class="bi bi-clock text-muted me-2"></i>
                                        <strong>Время разработки:</strong> <?= htmlspecialchars($infoDevelopTime) ?>
                                    </li>
                                <?php endif; ?>

                                <?php if ($infoVersion !== ''): ?>
                                    <li class="mb-3">
                                        <i class="bi bi-code-slash text-muted me-2"></i>
                                        <strong>Версия:</strong> <?= htmlspecialchars($infoVersion) ?>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <?php if ($infoIndex < count($projectInfoItems) - 1): ?>
                                <hr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3">
                                <i class="bi bi-calendar-event text-muted me-2"></i>
                                <strong>Дата:</strong> <?= htmlspecialchars($formatDate((string) ($currentProject->created_at ?? ''))) ?>
                            </li>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($currentProject->links)): ?>
                        <h4 class="card-title h5 mt-4 mb-3">Ссылки</h4>
                        <div class="d-grid gap-2">
                            <?php foreach ($currentProject->links as $link): ?>
                                <a href="<?= htmlspecialchars((string) ($link->link ?? '')) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-bug-fill me-2"></i><?= htmlspecialchars((string) ($link->name ?? '')) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Навигация между проектами -->
    <div class="row mt-5 pt-4 border-top">
        <div class="col-12 text-center">
            <p class="text-muted mb-4">Посмотрите другие мои проекты:</p>

            <?php $projectsGrid->render(); ?>
        </div>
    </div>
</div>