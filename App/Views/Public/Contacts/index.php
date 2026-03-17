<?php
    \Modules\Main\AssetLoader::getInstance()->addJs('/Templates/Inner/scripts/feedback.js');
?>

<div class="container mt-5">
    <div class="row">
        <!-- Форма обратной связи -->
        <div class="col-md-8 mb-4 mb-md-0">
            <h2 class="mb-4">Свяжитесь со мной</h2>
            <form id="message-form" class="form-control p-4">
                <div class="mb-3">
                    <label for="name" class="form-label">Ваше имя</label>
                    <input type="text" class="form-control" id="name" placeholder="Введите ваше имя" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="example@example.com" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Сообщение</label>
                    <textarea class="form-control" id="message" rows="5" placeholder="Опишите ваш вопрос или предложение..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Отправить сообщение</button>
            </form>
        </div>

        <!-- Контакты -->
        <div class="col-md-4">
            <h3 class="mb-4">Мои контакты</h3>
            <div class="btn-group btn-group-justified mb-3">
                <a href="https://github.com/LancelotOzerny/" type="button" class="btn btn-outline-secondary">
                    <i class="bi bi-github"></i> GitHub
                </a>
                <a href="https://t.me/lalalancy" type="button" class="btn btn-outline-secondary">
                    <i class="bi bi-telegram"></i> Telegram
                </a>
                <a href="https://vk.com/lancy2002" type="button" class="btn btn-outline-secondary">
                    <i class="bi bi-vk"></i> VK
                </a>
            </div>
            <div class="gap-4">
                <a href="tel:89205201831" class="btn btn-outline-secondary">
                    <i class="bi bi-telephone"></i> Телефон
                </a>
                <a href="mailto:lancelot.ozernuy@gmail.com" class="btn btn-outline-secondary">
                    <i class="bi bi-envelope"></i> Email
                </a>
            </div>
        </div>
    </div>
</div>

<div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto toast-title">Уведомление</strong>
            <small class="toast-time">Сейчас</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>
