<?php
/* @var array $data */

$project = $data['project'] ?? null;
$tags = $data['tags'] ?? [];
$links = $data['links'] ?? [];
$projectInfo = $data['projectInfo'] ?? [];

if ($project === null) {
    echo '<div class="alert alert-danger">Проект не найден.</div>';
    return;
}

$isActive = (int) ($project->active ?? 0) === 1;
?>

<section class="admin-project-detail">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Редактирование проекта #<?= (int) ($project->id ?? 0) ?></h1>
            <p class="text-secondary mb-0"><?= htmlspecialchars((string) ($project->name ?? 'Без названия')) ?></p>
        </div>
        <a href="/admin/projects/" class="btn btn-outline-secondary">К списку проектов</a>
    </div>

    <form action="#" method="post" class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 pb-0">
            <ul class="nav nav-tabs card-header-tabs" id="project-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-main-link" data-bs-toggle="tab" data-bs-target="#tab-main" type="button" role="tab">Основная информация</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-preview-link" data-bs-toggle="tab" data-bs-target="#tab-preview" type="button" role="tab">Preview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-detail-link" data-bs-toggle="tab" data-bs-target="#tab-detail" type="button" role="tab">Detail</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-other-link" data-bs-toggle="tab" data-bs-target="#tab-other" type="button" role="tab">Другое</button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content pt-2" id="project-tabs-content">
                <div class="tab-pane fade show active" id="tab-main" role="tabpanel" aria-labelledby="tab-main-link">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">ID</label>
                            <input type="text" class="form-control" value="<?= (int) ($project->id ?? 0) ?>" readonly>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Создан</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($project->created_at ?? '')) ?>" readonly>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Изменен</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($project->changed_at ?? '')) ?>" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Название проекта</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars((string) ($project->name ?? '')) ?>">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="active" name="active" <?= $isActive ? 'checked' : '' ?>>
                                <label class="form-check-label" for="active">Активный проект</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-preview" role="tabpanel" aria-labelledby="tab-preview-link">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Preview image URL</label>
                            <input type="text" name="preview_image_url" class="form-control" value="<?= htmlspecialchars((string) ($project->preview_image_url ?? '')) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Preview text</label>
                            <textarea name="preview_text" rows="6" class="form-control"><?= htmlspecialchars((string) ($project->preview_text ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-detail" role="tabpanel" aria-labelledby="tab-detail-link">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Detail image URL</label>
                            <input type="text" name="detail_image_url" class="form-control" value="<?= htmlspecialchars((string) ($project->detail_image_url ?? '')) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Detail text</label>
                            <textarea name="detail_text" rows="10" class="form-control"><?= htmlspecialchars((string) ($project->detail_text ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-other" role="tabpanel" aria-labelledby="tab-other-link">
                    <div class="mb-4">
                        <h3 class="h6 mb-3">Теги</h3>
                        <?php if (empty($tags)): ?>
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Tag ID</label>
                                    <input type="text" class="form-control" value="">
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label">Название тега</label>
                                    <input type="text" class="form-control" value="">
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($tags as $index => $tag): ?>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Tag ID</label>
                                        <input type="text" name="tags[<?= $index ?>][id]" class="form-control" value="<?= (int) ($tag->id ?? 0) ?>">
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <label class="form-label">Название тега</label>
                                        <input type="text" name="tags[<?= $index ?>][name]" class="form-control" value="<?= htmlspecialchars((string) ($tag->name ?? '')) ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h3 class="h6 mb-3">Ссылки проекта</h3>
                        <?php if (empty($links)): ?>
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Название</label>
                                    <input type="text" class="form-control" value="">
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label">Ссылка</label>
                                    <input type="text" class="form-control" value="">
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($links as $index => $link): ?>
                                <div class="border rounded p-3 mb-3">
                                    <div class="row g-2">
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">ID</label>
                                            <input type="text" class="form-control" value="<?= (int) ($link->id ?? 0) ?>" readonly>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Название</label>
                                            <input type="text" name="links[<?= $index ?>][name]" class="form-control" value="<?= htmlspecialchars((string) ($link->name ?? '')) ?>">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Ссылка</label>
                                            <input type="text" name="links[<?= $index ?>][link]" class="form-control" value="<?= htmlspecialchars((string) ($link->link ?? '')) ?>">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">project_id</label>
                                            <input type="text" class="form-control" value="<?= (int) ($link->project_id ?? 0) ?>" readonly>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">created_at</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($link->created_at ?? '')) ?>" readonly>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">changed_at</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars((string) ($link->changed_at ?? '')) ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h3 class="h6 mb-3">Детальная информация проекта</h3>
                        <?php if (empty($projectInfo)): ?>
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Дата</label>
                                    <input type="date" class="form-control" value="">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Время разработки</label>
                                    <input type="text" class="form-control" value="">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Версия</label>
                                    <input type="text" class="form-control" value="">
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($projectInfo as $index => $item): ?>
                                <div class="border rounded p-3 mb-3">
                                    <div class="row g-2">
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">ID</label>
                                            <input type="text" class="form-control" value="<?= (int) ($item->id ?? 0) ?>" readonly>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">project_id</label>
                                            <input type="text" class="form-control" value="<?= (int) ($item->project_id ?? 0) ?>" readonly>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">Дата</label>
                                            <input type="date" name="project_info[<?= $index ?>][date]" class="form-control" value="<?= htmlspecialchars((string) ($item->date ?? '')) ?>">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">Время разработки</label>
                                            <input type="text" name="project_info[<?= $index ?>][develop_time]" class="form-control" value="<?= htmlspecialchars((string) ($item->develop_time ?? '')) ?>">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">Версия</label>
                                            <input type="text" name="project_info[<?= $index ?>][version]" class="form-control" value="<?= htmlspecialchars((string) ($item->version ?? '')) ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-top d-flex justify-content-end">
            <button type="button" class="btn btn-primary" disabled>Сохранить (в разработке)</button>
        </div>
    </form>
</section>
