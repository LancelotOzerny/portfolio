<section class="admin-settings">
	<div class="card border-0 shadow-sm">
		<div class="card-body p-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h1 class="h4 mb-0">Настройки</h1>
				<a href="/admin/" class="btn btn-outline-secondary btn-sm">Назад в админку</a>
			</div>

			<div class="row g-3">
				<div class="col-12 col-md-6">
					<a href="/admin/settings/configs/" class="card h-100 text-decoration-none border shadow-none">
						<div class="card-body">
							<p class="small text-uppercase text-secondary mb-1">Подпункт настроек</p>
							<h2 class="h5 text-dark mb-2">Конфиги</h2>
							<p class="text-secondary mb-0">Редактирование конфигурационных файлов проекта.</p>
						</div>
					</a>
				</div>
				<div class="col-12 col-md-6">
					<a href="/admin/settings/repository/" class="card h-100 text-decoration-none border shadow-none">
						<div class="card-body">
							<p class="small text-uppercase text-secondary mb-1">Подпункт настроек</p>
							<h2 class="h5 text-dark mb-2">Репозиторий</h2>
							<p class="text-secondary mb-0">Обновление рабочей копии проекта из ветки main.</p>
						</div>
					</a>
				</div>
				<div class="col-12 col-md-6">
					<div class="card h-100 border shadow-none">
						<div class="card-body">
							<p class="small text-uppercase text-secondary mb-1">Подпункт настроек</p>
							<h2 class="h5 text-dark mb-2">
								<a href="/admin/settings/backup/" class="text-dark text-decoration-none">Резервное копирование</a>
							</h2>
							<p class="text-secondary mb-3">Создание, восстановление и удаление резервных копий проекта.</p>
							<div class="d-flex flex-wrap gap-2">
								<a href="/admin/settings/backup/create/" class="btn btn-outline-primary btn-sm">Создание копии</a>
								<a href="/admin/settings/backup/list/" class="btn btn-outline-secondary btn-sm">Список копий</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

