<div class="text-center py-5 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <h1 class="display-3 fw-bold">Портфолио</h1>
    <p class="lead fs-4">в котором вы можете найти разные работы на разные тематики</p>
</div>

<div class="filter-buttons text-center mt-5">
    <button class="btn btn-outline-primary active" data-filter="all">Все проекты</button>
    <button class="btn btn-outline-primary" data-filter="web">Веб‑разработка</button>
    <button class="btn btn-outline-primary" data-filter="mobile">Мобильные приложения</button>
    <button class="btn btn-outline-primary" data-filter="design">Дизайн</button>
    <button class="btn btn-outline-primary" data-filter="ui">UI/UX</button>
    <button class="btn btn-outline-primary" data-filter="ecommerce">E‑commerce</button>
</div>

<main class="py-5">
    <div class="container">
        <div class="row g-4">
            <?php for ($i = 0; $i < 10; ++$i): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow hover-lift position-relative overflow-hidden">
                        <img src="https://avtor24.ru/assets/files/articles/individual-project-inline-new.jpg" class="card-img-top" alt="Проект">
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold mb-3 fs-5">Онлайн-магазин книг</h5>
                            <p class="card-text text-muted flex-grow-1 mb-3">Корпоративный интернет-магазин с каталогом книг, слайдером новинок и адаптивной версткой на Bootstrap.</p>
                            <div class="d-flex gap-2 mb-4 flex-wrap">
                                <span class="badge bg-secondary">PHP</span>
                                <span class="badge bg-secondary">Bootstrap</span>
                                <span class="badge bg-secondary">jQuery</span>
                                <span class="badge bg-secondary">1C-Bitrix</span>
                            </div>
                            <div class="d-flex gap-3">
                                <a href="#" class="btn btn-outline-primary flex-fill py-2 fs-6 fw-medium" target="_blank">DEMO</a>
                                <a href="#" class="btn btn-primary flex-fill py-2 fs-6 fw-medium" target="_blank">GIT</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</main>