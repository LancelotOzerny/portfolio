<main class="container my-5">
    <!-- Секция с фото и кратким описанием -->
    <section class="row mb-5 align-items-center">
        <div class="col-md-4 text-center mb-4 mb-md-0">
            <!-- Замените src на путь к вашему фото -->
            <div class="position-relative mx-auto mb-4" style="width: 320px; height: 320px;">
                <img src="/upload/images/main/profile.png"
                     alt="Фото профиля"
                     class="profile-image img-fluid rounded-circle shadow-lg position-absolute"
                     style="width: 300px; height: 300px; object-fit: cover; top: 10px; left: 10px; z-index: 2; border: 5px solid #fff;">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary rounded-circle"
                     style="z-index: 1; opacity: 0.15;"></div>
            </div>
        </div>
        <div class="col-md-8">
            <h2>Привет, я Максим Беляков</h2>
            <p class="fs-5">Я FullStack-Developer. Занимаюсь разработкой сайтов уже 2 года.</p>
            <p>Мои ключевые навыки включают PHP, MySQL, HTML, Less, jQuery. Я стремлюсь к постоянному развитию и изучению новых технологий.</p>
        </div>
    </section>

    <?php
    (new \Components\Resume\Resume([
        'show_experience' => true,
    ]))->render();
    ?>

    <!-- Секция о навыках -->
    <section class="scroll-show-area mb-5">
        <h3 class="border-bottom pb-2 mb-4">Технологический стек</h3>
        <ul class="list-group">
            <li class="list-group-item"><b>Backend:</b> PHP (1C-Bitrix), Sql (MySQL)</li>
            <li class="list-group-item"><b>Frontend:</b> Html (Pug), Css (Less, Bootstrap), Js (jQuery, TypeScript)</li>
            <li class="list-group-item"><b>Другое:</b> Gulp, Git (Github), Composer, Linux, REST API</li>
        </ul>
    </section>

    <!-- Секция об образовании -->
    <section class="scroll-show-area">
        <h3 class="border-bottom pb-2 mb-4">Образование</h3>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Волгоградский Государственный Университет (ВолГУ)</h5>
                <h6 class="card-subtitle mb-2 text-muted">Информационные системы и технологии, 2020-2024</h6>
                <p class="card-text">
                    За время обучения по специальности «Информационные системы и технологии» в ВолГУ
                    я научился программировать, проектировать информационные системы и работать с базами данных.
                    Освоил веб‑разработку, кибербезопасность и облачные технологии, изучил сетевые решения.
                    Научился управлять IT‑проектами и эффективно работать в команде.
                </p>
            </div>
        </div>
    </section>
</main>
