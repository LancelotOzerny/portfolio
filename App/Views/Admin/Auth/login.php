<?php
	\Modules\Main\AssetLoader::getInstance()->addJs('/Templates/Admin/scripts/auth.js');
?>

<section class="d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 6rem);">
	<div class="w-100" style="max-width: 520px;">
		<div class="card shadow-sm border-0">
			<div class="card-body p-4 p-md-5">
				<h1 class="h3 mb-2">Авторизация</h1>
				<p class="text-secondary mb-4">Войдите, чтобы получить доступ к панели администратора.</p>

				<div id="auth-alert" class="alert alert-danger d-none" role="alert"></div>

				<form id="auth-form" method="post" action="/api/auth/login/" class="d-grid gap-3">
					<div>
						<label for="login" class="form-label">Логин</label>
						<input id="login" name="login" type="text" class="form-control form-control-lg" required>
					</div>

					<div>
						<label for="password" class="form-label">Пароль</label>
						<input id="password" name="password" type="password" class="form-control form-control-lg" required>
					</div>

					<button id="auth-submit" type="submit" class="btn btn-primary btn-lg mt-2">Войти</button>
				</form>
			</div>
		</div>
	</div>
</section>
