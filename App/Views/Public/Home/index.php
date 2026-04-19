<?php
$heroVariant = 'v1'; // v1 | v2
?>

<?php if ($heroVariant === 'v1'): ?>
<div class="hero-area hero-area-v1 row py-5 align-items-center position-relative overflow-hidden">
    <div class="hero-grid-overlay" aria-hidden="true"></div>

    <div class="col-lg-6 text-center text-lg-start position-relative z-2 hero-content">
        <span class="hero-kicker mb-3">Web Development</span>
        <h1 class="display-3 fw-bold mb-4 text-shadow hero-title">Максим Беляков</h1>
        <p class="lead fs-3 mb-4 text-shadow hero-subtitle">Full-Stack Web Developer</p>
        <p class="text-muted fs-5 mb-5 pe-lg-5 hero-description">
            Разработка корпоративных порталов, игр и CMS на современных технологиях.
        </p>
        <div class="d-flex gap-3 flex-column flex-sm-row justify-content-center justify-content-lg-start hero-actions">
            <a href="/portfolio/" class="btn btn-primary btn-lg px-5 py-3 fs-5 shadow-lg">
                <i class="fas fa-briefcase me-2"></i>Проекты
            </a>
            <a href="/contacts/" class="btn btn-outline-primary btn-lg px-5 py-3 fs-5 shadow">
                <i class="fas fa-envelope me-2"></i>Связаться
            </a>
        </div>
    </div>

    <div class="col-lg-6 mt-5 mt-lg-0 position-relative z-2">
        <div class="hero-v1-visual mx-auto">
            <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1400&q=80"
                 alt="Рабочее место web-разработчика с кодом на экране"
                 class="hero-v1-image img-fluid">
            <div class="hero-v1-panel hero-v1-panel-a"><i class="fas fa-code me-2"></i>Frontend</div>
            <div class="hero-v1-panel hero-v1-panel-b"><i class="fas fa-server me-2"></i>Backend</div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="hero-area hero-area-v2 row py-5 align-items-center position-relative overflow-hidden">
    <div class="hero-grid-overlay" aria-hidden="true"></div>

    <div class="col-lg-7 text-center text-lg-start position-relative z-2 hero-content">
        <span class="hero-kicker mb-3">Build. Ship. Improve.</span>
        <h1 class="display-3 fw-bold mb-4 text-shadow hero-title">Максим Беляков</h1>
        <p class="lead fs-3 mb-4 text-shadow hero-subtitle">Full-Stack Web Developer</p>
        <p class="text-muted fs-5 mb-4 pe-lg-5 hero-description">
            Масштабируемые веб-решения с акцентом на производительность, стабильность и чистую архитектуру.
        </p>

        <div class="hero-v2-stats mb-4">
            <div class="hero-v2-stat"><span>95%</span> Frontend</div>
            <div class="hero-v2-stat"><span>75%</span> Backend</div>
            <div class="hero-v2-stat"><span>70%</span> Git</div>
        </div>

        <div class="d-flex gap-3 flex-column flex-sm-row justify-content-center justify-content-lg-start hero-actions">
            <a href="/portfolio/" class="btn btn-primary btn-lg px-5 py-3 fs-5 shadow-lg">
                <i class="fas fa-briefcase me-2"></i>Проекты
            </a>
            <a href="/contacts/" class="btn btn-outline-primary btn-lg px-5 py-3 fs-5 shadow">
                <i class="fas fa-envelope me-2"></i>Связаться
            </a>
        </div>
    </div>

    <div class="col-lg-5 mt-5 mt-lg-0 position-relative z-2">
        <div class="hero-v2-terminal mx-auto">
            <div class="hero-v2-terminal-head">
                <span class="hero-v2-dot"></span>
                <span class="hero-v2-dot"></span>
                <span class="hero-v2-dot"></span>
                <small class="ms-2">deploy.log</small>
            </div>
            <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1400&q=80"
                 alt="Монитор с кодом и интерфейсом разработки"
                 class="hero-v2-image img-fluid">
            <div class="hero-v2-status">
                <span class="hero-v2-status-line"></span>
                Release stable
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- Раздел "О себе" -->
<section class="scroll-show-area py-8 bg-light">
    <div class="py-5">
        <div class="d-flex align-items-center mb-3">
            <hr class="flex-grow-1">
            <h2 class="mx-3 fs-1">Вкратце о себе</h2>
            <hr class="flex-grow-1">
        </div>
    </div>

    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Фото слева -->
            <div class="col-lg-4 text-center">
                <div class="position-relative mx-auto mb-4" style="width: 320px; height: 320px;">
                    <img src="/upload/images/main/profile.png"
                         alt="Фото профиля"
                         class="profile-image img-fluid rounded-circle shadow-lg position-absolute"
                         style="width: 300px; height: 300px; object-fit: cover; top: 10px; left: 10px; z-index: 2; border: 5px solid #fff;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary rounded-circle"
                         style="z-index: 1; opacity: 0.15;"></div>
                </div>
                <div class="">
                    <h3 class="fw-bold text-primary mb-2 fs-2">Максим Беляков</h3>
                    <p class="text-muted mb-0 fs-5">Full-Stack Developer</p>
                </div>
            </div>

            <!-- Контент справа -->
            <div class="col-lg-8">
                <p class="lead text-dark mb-5 lh-lg" style="max-width: 650px;">
                    Full-Stack разработчик из Липецка с фокусом на корпоративные порталы и 1C-Bitrix.
                    Специализируюсь на создании масштабируемых веб-приложений с адаптивным дизайном.
                </p>

                <div class="row g-4 mb-6">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-white rounded-3 shadow-sm">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-4"
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-users fs-5 text-white"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 fs-5">Командная работа</h6>
                                <p class="text-muted mb-0 fs-6">Опыт работы в команде 100%</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-white rounded-3 shadow-sm">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-4"
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-rocket fs-5 text-white"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 fs-5">Frontend</h6>
                                <p class="text-muted mb-0 fs-6">HTML/CSS/JS 95%</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-white rounded-3 shadow-sm">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-4"
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-database fs-5 text-white"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 fs-5">Backend</h6>
                                <p class="text-muted mb-0 fs-6">PHP/Bitrix 75%</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-white rounded-3 shadow-sm">
                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-4"
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-code-branch fs-5 text-dark"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 fs-5">Инструменты</h6>
                                <p class="text-muted mb-0 fs-6">Git/Gulp/Composer</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center text-lg-start">
                    <a href="/upload/docs/resume.pdf" class="btn btn-primary btn-lg px-6 py-3 fs-5 fw-semibold shadow-lg" download>
                        <i class="fas fa-file-download me-2"></i>Скачать резюме
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Раздел навыков -->
<section class="scroll-show-area py-5 bg-light">
    <div class="py-5">
        <div class="d-flex align-items-center mb-3">
            <hr class="flex-grow-1">
            <h2 class="mx-3 fs-1">Технологический стек</h2>
            <hr class="flex-grow-1">
        </div>
        <p class="text-center text-muted">Навыки, которыми я овладел или изучаю в текущий момент</p>
    </div>

    <div class="container">
        <div class="row g-4 justify-content-center">
            <!-- Frontend -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-code me-2 text-primary"></i>Frontend
                        </h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark small">HTML / CSS (Bootstrap, LESS)</label>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient" style="width: 95%" role="progressbar" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-1">95%</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark small">JavaScript</label>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient" style="width: 40%" role="progressbar"
                                     aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-1">40%</div>
                        </div>
                        <div class="ms-3 mb-3">
                            <label class="form-label fw-normal text-muted small">Query</label>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-gradient" style="width: 35%"
                                     role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-n2">35%</div>
                        </div>
                        <div class="ms-3">
                            <label class="form-label fw-normal text-muted small">TypeScript</label>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-gradient" style="width: 40%" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-n2">40%</div>
                        </div>
                        <div class="mb-3 mt-2">
                            <label class="form-label fw-medium text-dark small">Gulp</label>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient" style="width: 100%"
                                     role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-1">100%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backend -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-server me-2 text-primary"></i>Backend
                        </h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark small">PHP</label>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient" style="width: 75%"
                                     role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-1">75%</div>
                        </div>
                        <div class="ms-3 mb-3">
                            <label class="form-label fw-normal text-muted small">1C-Bitrix</label>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-gradient" style="width: 60%"
                                     role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-n2">60%</div>
                        </div>
                        <div class="ms-3 mb-3">
                            <label class="form-label fw-normal text-muted small">Composer</label>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-gradient" style="width: 90%"
                                     role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-n2">90%</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark small">MySQL</label>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient" style="width: 60%"
                                     role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-1">60%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Другое -->
            <div class="col-lg-4 col-md-12">
                <div class="card h-100 border-0 shadow">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-tools me-2 text-primary"></i>Другое
                        </h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark small">Git</label>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient" style="width: 70%" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-1">70%</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark small">Паттерны проектирования</label>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient" style="width: 50%" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-1">50%</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-dark small">Linux</label>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient" style="width: 40%" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-end small text-muted mt-1">40%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Раздел проектов -->
<section class="scroll-show-area py-5 bg-white">
    <div class="py-5">
        <div class="d-flex align-items-center mb-3">
            <hr class="flex-grow-1">
            <h2 class="mx-3 fs-1">Проекты</h2>
            <hr class="flex-grow-1">
        </div>
        <p class="text-center text-muted">Прогресс моего обучения, в виде реализованных проектов</p>
    </div>

    <div class="container">
        <?php
        $projectsGrid = new \Components\ProjectsGrid\ProjectsGrid([
            'use_filters' => false,
            'limit' => 10
        ]);

        $projectsGrid->render();
        ?>
    </div>
</section>

<!-- Готов к новым проектам -->
<div class="scroll-show-area row py-5 text-center">
    <div class="col-12">
        <h2 class="fw-bold mb-4">Готов к новым проектам</h2>
        <a href="/contacts/" class="btn btn-outline-primary btn-lg px-5 py-3 fs-5">Связаться со мной</a>
    </div>
</div>
