<?php
$isFirstFilterElement = true;
?>

<!-- FILTERS -->
<?php if($this->getParam('use_filters')):?>
    <div id="projects-grid-filters" class="filter-buttons text-center mt-5">
        <?php foreach ($this->getParam('filters') as $key => $filter): ?>
            <button class="filter btn btn-outline-primary <?= $isFirstFilterElement ? 'active' : '' ?>" data-filter="filter-<?= $key ?>"><?= $filter ?></button>
            <?php
            $isFirstFilterElement = false;
        endforeach;
        ?>
    </div>
<?php endif; ?>

<!-- PROJECTS -->
<div class="projects-grid row g-4 <?php if($this->getParam('use_filters')) echo 'pt-5'?>">
    <?php foreach($this->getParam('items') ?? [] as $project): ?>
        <div class="project-item col-lg-4 col-md-6">
            <article class="project-card card h-100 border-0 shadow hover-lift position-relative overflow-hidden">
                <a class="project-card__media" href="/portfolio/projects/<?= $project->id ?>/" aria-label="<?= $project->name ?? '' ?>">
                    <img src="<?= !empty($project->preview_image_url) ? $project->preview_image_url : '/Components/ProjectsGrid/Default/img/no-image.webp' ?>"
                         class="project-card__image card-img-top" alt="<?= $project->name ?? '' ?>">
                </a>

                <div class="project-card__body card-body d-flex flex-column p-4">
                    <h5 class="project-card__title card-title fw-bold mb-3 fs-5"><?= $project->name ?></h5>
                    <p class="project-card__text card-text text-muted flex-grow-1 mb-3"><?= htmlspecialchars((string) ($project->preview_text ?? '')) ?></p>

                    <?php if($project->tags): ?>
                        <div class="project-card__tags d-flex gap-2 mb-4 flex-wrap">
                            <?php foreach ($project->tags as $tag): ?>
                                <span <?php if($this->getParam('use_filters')) echo "data-filter-target=\"filter-$tag->id\""?> class="project-card__tag badge bg-secondary"><?= $tag->name ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <a class="project-card__button btn btn-outline-primary flex-fill py-2 fs-6 fw-medium"
                       href="/portfolio/projects/<?= $project->id ?>/">Подробнее</a>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>
