<div class="wow row py-5 align-items-center" style="min-height: 80vh;">
    <div class="col-12 text-center position-relative">
        <h1 class="display-3 fw-bold mb-4 text-shadow">LANCY</h1>
        <p class="lead fs-3 mb-4 text-shadow">Full-Stack Web Developer</p>
        <p class="text-muted fs-5 mb-5 px-lg-5 mx-auto" style="max-width: 600px;">Разработка корпоративных порталов, игр и CMS на современных технологиях.</p>
        <div class="d-flex gap-3 flex-column flex-sm-row justify-content-center">
            <a href="portfolio.php" class="btn btn-primary btn-lg px-5 py-3 fs-5 shadow-lg">
                <i class="fas fa-briefcase me-2"></i>Проекты
            </a>
            <a href="contacts.php" class="btn btn-outline-primary btn-lg px-5 py-3 fs-5 shadow">
                <i class="fas fa-envelope me-2"></i>Связаться
            </a>
        </div>
    </div>
</div>

<style>
    .text-shadow { text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
    @media (max-width: 768px) {
        .position-absolute.start-0 { width: 220px; height: 220px; }
        img[alt="Lancy Studio"] { width: 200px; height: 200px; }
        [style*="padding-left: 20%"] { padding-left: 10% !important; padding-right: 10% !important; }
    }
</style>

<!-- Раздел "О себе" -->
<section class="wow py-8 bg-light">
    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Фото слева -->
            <div class="col-lg-4 text-center">
                <div class="position-relative mx-auto mb-4" style="width: 320px; height: 320px;">
                    <img src="https://avatars.mds.yandex.net/i?id=3dfc94dcbc11529b8f2840c8ab7ccb75_l-8492945-images-thumbs&n=13"
                         alt="Фото Lancy"
                         class="img-fluid rounded-circle shadow-lg position-absolute"
                         style="width: 300px; height: 300px; object-fit: cover; top: 10px; left: 10px; z-index: 2; border: 5px solid #fff;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary rounded-circle"
                         style="z-index: 1; opacity: 0.15;"></div>
                </div>
                <div class="">
                    <h3 class="fw-bold text-primary mb-2 fs-2">LANCY</h3>
                    <p class="text-muted mb-0 fs-5">Full-Stack Developer</p>
                </div>
            </div>

            <!-- Контент справа -->
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-5 fs-1">О себе</h2>
                <p class="lead text-dark mb-5 fs-4 lh-lg" style="max-width: 650px;">
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
                    <a href="resume.pdf" class="btn btn-primary btn-lg px-6 py-3 fs-5 fw-semibold shadow-lg" download>
                        <i class="fas fa-file-download me-2"></i>Скачать резюме
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .py-8 { padding-top: 6rem !important; padding-bottom: 6rem !important; }
    .mb-6 { margin-bottom: 4rem !important; }
    .fs-1 { font-size: calc(1.375rem + 1.5vw) !important; }
    .lh-lg { line-height: 1.8; }
</style>



<!-- Раздел навыков -->
    <section class="wow py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5 display-6">Навыки и компетенции</h2>

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
                                    <div class="progress-bar bg-gradient" style="width: 40%" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-end small text-muted mt-1">40%</div>
                            </div>
                            <div class="ms-3 mb-3">
                                <label class="form-label fw-normal text-muted small">• jQuery</label>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-gradient" style="width: 35%" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-end small text-muted mt-n2">35%</div>
                            </div>
                            <div class="ms-3">
                                <label class="form-label fw-normal text-muted small">• TypeScript</label>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-gradient" style="width: 40%" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-end small text-muted mt-n2">40%</div>
                            </div>
                            <div class="mb-3 mt-2">
                                <label class="form-label fw-medium text-dark small">Gulp</label>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-gradient" style="width: 100%" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
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
                                    <div class="progress-bar bg-gradient" style="width: 75%" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-end small text-muted mt-1">75%</div>
                            </div>
                            <div class="ms-3 mb-3">
                                <label class="form-label fw-normal text-muted small">• 1C-Bitrix</label>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-gradient" style="width: 60%" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-end small text-muted mt-n2">60%</div>
                            </div>
                            <div class="ms-3 mb-3">
                                <label class="form-label fw-normal text-muted small">• Composer</label>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-gradient" style="width: 90%" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-end small text-muted mt-n2">90%</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium text-dark small">MySQL</label>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-gradient" style="width: 60%" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
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
<section class="wow py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Проекты</h2>
            <p class="lead text-muted">Портфолио реализованных решений</p>
        </div>

        <div class="row g-4">
            <!-- Шаблон проекта (повторить 10 раз) -->
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

            <!-- Повтори этот блок 9 раз, меняя только: -->
            <!-- h5.card-title: название проекта -->
            <!-- p.card-text: описание -->
            <!-- badge: теги (PHP, JS, HTML, CSS, Bitrix, MySQL, Gulp, Git и т.д.) -->
        </div>
    </div>
</section>

<style>
    .hover-lift {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-lift:hover {
        transform: translateY(-12px);
        box-shadow: 0 25px 50px rgba(0,0,0,0.2) !important;
    }
    .hover-lift:hover .card-img-top {
        transform: scale(1.08);
    }
    .hover-lift .card-img-top {
        transition: transform 0.4s ease;
        height: 220px;
        object-fit: cover;
    }
    .badge { font-size: 0.75rem; padding: 0.4em 0.7em; }
</style>


<style>
        .hover-lift {
            transition: all 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }
        .hover-lift:hover .card-img-top {
            transform: scale(1.05);
        }
        .hover-lift .card-img-top {
            transition: transform 0.3s ease;
        }
        .card-title { color: #212529; font-size: 1.25rem; }
    </style>


    <div class="wow row py-5 text-center">
        <div class="col-12">
            <h2 class="fw-bold mb-4">Готов к новым проектам</h2>
            <a href="contacts.php" class="btn btn-outline-primary btn-lg px-5 py-3 fs-5">Связаться со мной</a>
        </div>
    </div>

    <style>
        .bg-gradient {
            background: linear-gradient(90deg, #007bff, #0056b3) !important;
        }
    </style>
