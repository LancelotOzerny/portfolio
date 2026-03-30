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
<div class="row g-4 <?php if($this->getParam('use_filters')) echo 'pt-5'?>">
    <?php foreach($this->getParam('items') ?? [] as $project): ?>
        <div class="project-item col-lg-4 col-md-6">
            <div class="card h-100 border-0 shadow hover-lift position-relative overflow-hidden">
                <img src="<?= $project->preview_image_url ?: '/Templates/Inner/img/no-image.webp' ?>"
                     class="card-img-top" alt="<?= $project->name ?? '' ?>">

                <div class="card-body d-flex flex-column p-4">
                    <h5 class="card-title fw-bold mb-3 fs-5"><?= $project->name ?></h5>
                    <p class="card-text text-muted flex-grow-1 mb-3"><?= $project->preview_text ?></p>

                    <?php if($project->tags): ?>
                        <div class="d-flex gap-2 mb-4 flex-wrap">
                            <?php foreach ($project->tags as $tag): ?>
                                <span <?php if($this->getParam('use_filters')) echo "data-filter-target=\"filter-$tag->id\""?> class="badge bg-secondary"><?= $tag->name ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <a class="btn btn-outline-primary flex-fill py-2 fs-6 fw-medium"
                       href="/portfolio/projects/<?= $project->id ?>/">Подробнее</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>