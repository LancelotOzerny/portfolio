<?php
/* @var array $data */

$albums = $data['albums'] ?? [];
$uploaded = (bool) ($data['uploaded'] ?? false);
$deleted = (bool) ($data['deleted'] ?? false);
$created = (bool) ($data['created'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
?>

<section class="admin-gallery">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">Контент</div>
			<h1 class="h3 mb-0">Галерея</h1>
		</div>
		<button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#createAlbumModal">Новый альбом</button>
	</div>

	<?php if ($uploaded): ?>
		<div class="alert alert-success">Фотография загружена.</div>
	<?php endif; ?>
	<?php if ($deleted): ?>
		<div class="alert alert-success">Фотография удалена.</div>
	<?php endif; ?>
	<?php if ($created): ?>
		<div class="alert alert-success">Альбом создан.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php if (empty($albums)): ?>
		<div class="alert alert-light border">Альбомов пока нет. Создайте альбом, чтобы загрузить фотографии.</div>
	<?php else: ?>
		<?php foreach ($albums as $album): ?>
			<?php
			$albumName = (string) ($album['name'] ?? '');
			$photos = $album['photos'] ?? [];
			?>
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-body p-4">
					<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
						<h2 class="h5 mb-0"><?= htmlspecialchars($albumName) ?></h2>
						<span class="badge text-bg-light border"><?= count($photos) ?> фото</span>
					</div>

					<?php if ($photos === []): ?>
						<p class="text-secondary mb-3">В этом альбоме пока нет фотографий.</p>
					<?php else: ?>
						<div class="row g-3 mb-4">
							<?php foreach ($photos as $photo): ?>
								<?php
								$photoName = (string) ($photo['name'] ?? '');
								$photoPath = (string) ($photo['path'] ?? '');
								?>
								<div class="col-6 col-md-4 col-lg-3 col-xl-2">
									<div class="admin-gallery__photo card h-100 border">
										<img
											class="card-img-top admin-gallery__photo-image"
											src="<?= htmlspecialchars($photoPath) ?>"
											alt="<?= htmlspecialchars($photoName) ?>"
										>
										<div class="card-body p-2">
											<p class="small text-truncate mb-2" title="<?= htmlspecialchars($photoName) ?>">
												<?= htmlspecialchars($photoName) ?>
											</p>
											<form
												method="post"
												action="/admin/content/gallery/delete/"
												onsubmit="return confirm('Удалить это фото?');"
											>
												<input type="hidden" name="album" value="<?= htmlspecialchars($albumName) ?>">
												<input type="hidden" name="filename" value="<?= htmlspecialchars($photoName) ?>">
												<button class="btn btn-outline-danger btn-sm w-100" type="submit">Удалить</button>
											</form>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<form class="row g-2 align-items-end" method="post" action="/admin/content/gallery/upload/" enctype="multipart/form-data">
						<input type="hidden" name="album" value="<?= htmlspecialchars($albumName) ?>">
						<div class="col-md-8 col-lg-9">
							<label class="form-label mb-1" for="gallery-upload-<?= md5($albumName) ?>">Загрузить фото</label>
							<input
								class="form-control"
								type="file"
								id="gallery-upload-<?= md5($albumName) ?>"
								name="photo"
								accept="image/jpeg,image/png,image/gif,image/webp"
								required
							>
						</div>
						<div class="col-md-4 col-lg-3">
							<button class="btn btn-primary w-100" type="submit">Загрузить</button>
						</div>
					</form>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</section>

<style>
	.admin-gallery__photo-image {
		aspect-ratio: 1;
		object-fit: cover;
	}
</style>

<div class="modal fade" id="createAlbumModal" tabindex="-1" aria-labelledby="createAlbumModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<form class="modal-content" method="post" action="/admin/content/gallery/album/create/">
			<div class="modal-header">
				<h2 class="modal-title fs-5" id="createAlbumModalLabel">Новый альбом</h2>
				<button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Закрыть"></button>
			</div>
			<div class="modal-body">
				<label class="form-label" for="gallery-album-name">Название альбома</label>
				<input class="form-control" type="text" id="gallery-album-name" name="name" required maxlength="120">
				<div class="form-text">Будет создана папка в /upload/gallery/</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Отмена</button>
				<button class="btn btn-primary" type="submit">Создать</button>
			</div>
		</form>
	</div>
</div>
