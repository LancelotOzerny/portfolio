</div>
<footer class="bg-dark text-light mt-5 py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-12 mb-4">
                <h5 class="fw-bold mb-3">LANCY</h5>
                <p>Создаю современные веб-решения для вашего бизнеса с использованием PHP, MySQL, HTML, Bootstrap и jQuery.</p>
                <div class="d-flex gap-2">
                    <a href="#" class="text-light" title="VK"><i class="fab fa-vk fs-5"></i></a>
                    <a href="#" class="text-light" title="Telegram"><i class="fab fa-telegram fs-5"></i></a>
                    <a href="#" class="text-light" title="Instagram"><i class="fab fa-instagram fs-5"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <?php (new \Components\Navigation\Navigation())->setParam('template', 'MapColumn')->render(); ?>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Контакты</h6>
                <p>Липецк, Россия</p>
                <p>
                    <a href="tel:89205201831" class='text-white' style='text-decoration: none;'>
                        8 (920) 520 18 31
                    </a>
                </p>
                <p>
                    <a href="mailto:lancelot.ozernuy@gmail.com" class='text-white' style='text-decoration: none;'>
                        lancelot.ozernuy@gmail.com
                    </a>
                </p>
            </div>
        </div>
        <hr class="my-4 opacity-25">
        <div class="text-center">
            <p class="mb-0">&copy; 2026 LANCY. Все права защищены.</p>
        </div>
    </div>
</footer>

<script src="/Templates/Default/js/bootstrap.bundle.min.js"></script>
<script src="/Templates/Default/js/jquery-4.0.0.min.js"></script>
<script src="/Templates/Default/js/main.js"></script>
</body>
</html>
