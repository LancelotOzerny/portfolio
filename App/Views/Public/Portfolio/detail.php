<?php
/* @var array $data */

$projectsGrid = new \Components\ProjectsGrid\ProjectsGrid([
    'use_filters' => false,
    'limit' => 3
]);

$currentProject = $data['info'];
$createdDate = new DateTime($currentProject->created_at);
?>

<div class="container pt-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold mb-4"><?= $currentProject->name ?></h1>

            <p class="fs-5 lh-lg">
                <?= $currentProject->preview_text ?>
            </p>

            <?php if(count($currentProject->tags)): ?>
            <div class="d-flex gap-3 mb-5 flex-wrap">
                <?php foreach ($currentProject->tags as $tag): ?>
                <span class="badge bg-primary"><?= $tag->name ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">

        <div class="col-lg-8">
            <?php if ($data['info']->detail_image_url): ?>
            <img    src="<?= $data['info']->detail_image_url ?>" 
                    alt="<?= $data['info']->name ?>"
                    title="<?= $data['info']->name ?>"
                    style="width: 100%;">
            <?php endif; ?>

            <?= $currentProject->detail_text ?>
        </div>

        <!-- Боковая панель -->
        <div class="col-lg-4">
            <div class="card border-0 shadow hover-lift sticky-top" style="top: 96px;">
                <div class="card-body">
                    <h4 class="card-title h5 mb-3">Детали проекта</h4>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-3">
                            <i class="bi bi-calendar-event text-muted me-2"></i>
                            <strong>Дата:</strong> <?= (new DateTime($currentProject->created_at))->format('d/m/Y') ?>
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-clock text-muted me-2"></i>
                            <strong>Время разработки:</strong> 3 недели
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-code-slash text-muted me-2"></i>
                            <strong>Версия:</strong> 1.2.0
                        </li>
                    </ul>

                    <?php if(count($currentProject->links)): ?>
                    <h4 class="card-title h5 mt-4 mb-3">Ссылки</h4>
                    <div class="d-grid gap-2">
                        <?php foreach ($currentProject->links as $link): ?>
                            <a href="<?= $link->link ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-bug-fill me-2"></i><?= $link->name ?>
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