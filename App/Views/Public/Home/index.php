
<nav class="navbar navbar-expand-lg sticky-top navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">LANCY</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Портфолио</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Контакты</a>
                </li>
            </ul>
            <!-- Соцсети - круглые кнопки -->
            <div class="navbar-social ms-auto ms-lg-3">
                <a href="#" class="social-btn github" title="GitHub" aria-label="GitHub">
                    <i class="fab fa-github"></i>
                </a>
                <a href="#" class="social-btn telegram" title="Telegram" aria-label="Telegram">
                    <i class="fab fa-telegram-plane"></i>
                </a>
                <a href="#" class="social-btn linkedin" title="LinkedIn" aria-label="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
    /* Соцсети в navbar */
    .navbar-social {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .social-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .social-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        border-color: transparent;
    }

    .github:hover { background: #24292e; color: white; }
    .telegram:hover { background: #0088cc; color: white; }
    .linkedin:hover { background: #0077b5; color: white; }

    @media (max-width: 991px) {
        .navbar-social {
            margin-top: 1rem;
            justify-content: center;
        }
    }
</style>