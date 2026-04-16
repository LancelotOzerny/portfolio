<?php
/* @var array $data */

$projects = $data['projects'] ?? [];
?>

<section class="admin-projects">
    <style>
        .admin-project-card {
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .admin-project-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12) !important;
        }

        .admin-project-title-link {
            color: inherit;
            text-decoration: none;
        }

        .admin-project-title-link:hover {
            text-decoration: underline;
        }
    </style>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Проекты</h1>
        </div>
        <a href="/admin/" class="btn btn-outline-secondary">Назад в админку</a>
    </div>

    <?php if (empty($projects)): ?>
        <div class="alert alert-warning mb-0">Проекты не найдены.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($projects as $project): ?>
                <?php
                $previewText = trim((string) ($project->preview_text ?? ''));
                $shortPreviewText = strlen($previewText) > 200 ? substr($previewText, 0, 200) . '...' : $previewText;
                $previewImage = trim((string) ($project->preview_image_url ?? ''));
                $isActive = (int) ($project->active ?? 0) === 1;
                $projectId = (int) ($project->id ?? 0);
                $projectName = (string) ($project->name ?? 'Без названия');
                $projectTags = $project->tags ?? [];
                $tagNames = [];
                foreach ($projectTags as $tag) {
                    $name = trim((string) ($tag->name ?? ''));
                    if ($name !== '') {
                        $tagNames[] = $name;
                    }
                }
                $tagsText = empty($tagNames) ? 'не указаны' : implode(', ', $tagNames);
                ?>

                <div class="col-12">
                    <article class="card border-0 shadow-sm h-100 admin-project-card">
                        <div class="row g-0">
                            <div class="col-12 col-md-3">
                                <?php if ($previewImage !== ''): ?>
                                    <img src="<?= htmlspecialchars($previewImage) ?>" class="img-fluid rounded-start w-100" style="object-fit: cover; height: 220px; max-height: 300px;" alt="Превью проекта">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 220px; max-height: 300px;">
                                        Нет изображения
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 col-md-9">
                                <div class="card-body h-100 d-flex flex-column">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                        <h2 class="h5 mb-1">
                                            <a class="admin-project-title-link" href="/admin/projects/<?= $projectId ?>/">[<?= $projectId ?>] <?= htmlspecialchars($projectName) ?></a>
                                        </h2>
                                        <span class="badge <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                            <?= $isActive ? 'Активен' : 'Скрыт' ?>
                                        </span>
                                    </div>

                                    <p class="text-secondary mb-3"><?= htmlspecialchars($shortPreviewText !== '' ? $shortPreviewText : 'Описание не заполнено') ?></p>

                                    <div class="small text-secondary mb-3">
                                        <div>Создан: <?= htmlspecialchars((string) ($project->created_at ?? '-')) ?></div>
                                        <div>Изменен: <?= htmlspecialchars((string) ($project->changed_at ?? '-')) ?></div>
                                        <div>Теги: <?= htmlspecialchars($tagsText) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
