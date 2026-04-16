<section class="d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 6rem);">
    <div class="w-100" style="max-width: 520px;">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-2">Страница авторизации</h1>
                <p class="text-secondary mb-4">Войдите чтобы попасть в административную страницу</p>

                <form method="post" action="/admin/login/" class="d-grid gap-3">
                    <div>
                        <label for="login" class="form-label">Логин</label>
                        <input id="login" name="login" type="text" class="form-control form-control-lg" required>
                    </div>

                    <div>
                        <label for="password" class="form-label">Пароль</label>
                        <input id="password" name="password" type="password" class="form-control form-control-lg" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg mt-2">Авторизоваться</button>
                </form>
            </div>
        </div>
    </div>
</section>
