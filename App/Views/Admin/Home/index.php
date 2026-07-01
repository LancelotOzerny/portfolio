<?php
/* @var array $data */

$projectsCount = (int) ($data['projectsCount'] ?? 0);
$usersCount = (int) ($data['usersCount'] ?? 0);
?>

<section class="admin-dashboard">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h4 mb-2">Главная</h2>
            <p class="text-secondary mb-0">Используйте меню слева для перехода по разделам админ-панели.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="small text-uppercase text-secondary mb-1">Проекты</p>
                    <p class="h3 mb-0"><?= $projectsCount ?></p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="small text-uppercase text-secondary mb-1">Пользователи</p>
                    <p class="h3 mb-0"><?= $usersCount ?></p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="small text-uppercase text-secondary mb-1">Сообщения</p>
                    <p class="h5 mb-0">Скоро...</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="small text-uppercase text-secondary mb-1">Ошибки</p>
                    <p class="h5 mb-0">Скоро...</p>
                </div>
            </div>
        </div>
    </div>
</section>
