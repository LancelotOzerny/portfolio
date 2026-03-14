</div>
<footer class="bg-dark text-light mt-5 py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="fw-bold mb-3">LANCY</h5>
                <p>Описание компании. Мы создаем современные веб-решения для вашего бизнеса с использованием HTML, Bootstrap и jQuery.</p>
                <div class="d-flex gap-2">
                    <a href="#" class="text-light" title="VK"><i class="fab fa-vk fs-5"></i></a>
                    <a href="#" class="text-light" title="Telegram"><i class="fab fa-telegram fs-5"></i></a>
                    <a href="#" class="text-light" title="Instagram"><i class="fab fa-instagram fs-5"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Меню</h6>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-light text-decoration-none">Главная</a></li>
                    <li><a href="about.php" class="text-light text-decoration-none">О нас</a></li>
                    <li><a href="services.php" class="text-light text-decoration-none">Услуги</a></li>
                    <li><a href="portfolio.php" class="text-light text-decoration-none">Портфолио</a></li>
                </ul>
            </div>
            <div class="col-lg-6 col-md-12 mb-4">
                <h6 class="fw-bold mb-3">Контакты</h6>
                <p><i class="fas fa-map-marker-alt me-2"></i>Липецк, Россия</p>
                <p><i class="fas fa-phone me-2"></i>+7 (4742) 00-00-00</p>
                <p><i class="fas fa-envelope me-2"></i>info@lancy.ru</p>
            </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="text-center">
            <p class="mb-0">&copy; 2026 LANCY. Все права защищены.</p>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({scrollTop: $($(this).attr('href')).offset().top - 70}, 800);
        });
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('header').addClass('shadow-lg');
            } else {
                $('header').removeClass('shadow-lg');
            }
        });
    });
</script>
</body>
</html>
