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
			$albumKey = md5($albumName);
			?>
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-body p-4">
					<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
						<h2 class="h5 mb-0"><?= htmlspecialchars($albumName) ?></h2>
						<span class="badge text-bg-light border"><?= count($photos) ?> фото</span>
					</div>

					<div class="row g-3">
						<div class="col-6 col-md-4 col-lg-3 col-xl-2">
							<form
								class="admin-gallery__dropzone"
								method="post"
								action="/admin/content/gallery/upload/"
								enctype="multipart/form-data"
								data-gallery-dropzone
							>
								<input type="hidden" name="album" value="<?= htmlspecialchars($albumName) ?>">
								<input
									class="admin-gallery__dropzone-input"
									type="file"
									id="gallery-upload-<?= $albumKey ?>"
									name="photo"
									accept="image/jpeg,image/png,image/gif,image/webp"
									required
									hidden
								>
								<label class="admin-gallery__dropzone-area" for="gallery-upload-<?= $albumKey ?>">
									<span class="admin-gallery__dropzone-icon" aria-hidden="true">+</span>
									<span class="admin-gallery__dropzone-title">Загрузить фото</span>
									<span class="admin-gallery__dropzone-hint">Нажмите или перетащите сюда</span>
								</label>
							</form>
						</div>

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
										loading="lazy"
										decoding="async"
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

	.admin-gallery__dropzone {
		height: 100%;
		margin: 0;
	}

	.admin-gallery__dropzone-area {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		gap: 0.35rem;
		aspect-ratio: 1;
		width: 100%;
		padding: 0.75rem;
		border: 2px dashed #ced4da;
		border-radius: 0.375rem;
		background: #f8f9fa;
		color: #6c757d;
		text-align: center;
		cursor: pointer;
		transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease;
		user-select: none;
	}

	.admin-gallery__dropzone-area:hover,
	.admin-gallery__dropzone.is-dragover .admin-gallery__dropzone-area {
		border-color: #0d6efd;
		background: #eef4ff;
		color: #0d6efd;
	}

	.admin-gallery__dropzone-icon {
		font-size: 1.75rem;
		line-height: 1;
		font-weight: 300;
	}

	.admin-gallery__dropzone-title {
		font-size: 0.875rem;
		font-weight: 600;
	}

	.admin-gallery__dropzone-hint {
		font-size: 0.75rem;
		line-height: 1.2;
	}
</style>

<script>
	(() => {
		const dropzones = document.querySelectorAll('[data-gallery-dropzone]');

		dropzones.forEach((form) => {
			const input = form.querySelector('.admin-gallery__dropzone-input');
			const area = form.querySelector('.admin-gallery__dropzone-area');

			if (!(input instanceof HTMLInputElement) || !(area instanceof HTMLElement)) {
				return;
			}

			const submitSelectedFile = (file) => {
				if (!(file instanceof File)) {
					return;
				}

				const transfer = new DataTransfer();
				transfer.items.add(file);
				input.files = transfer.files;
				form.submit();
			};

			input.addEventListener('change', () => {
				if (!input.files || input.files.length === 0) {
					return;
				}

				form.submit();
			});

			['dragenter', 'dragover'].forEach((eventName) => {
				area.addEventListener(eventName, (event) => {
					event.preventDefault();
					event.stopPropagation();
					form.classList.add('is-dragover');
				});
			});

			['dragleave', 'dragend'].forEach((eventName) => {
				area.addEventListener(eventName, (event) => {
					event.preventDefault();
					event.stopPropagation();
					form.classList.remove('is-dragover');
				});
			});

			area.addEventListener('drop', (event) => {
				event.preventDefault();
				event.stopPropagation();
				form.classList.remove('is-dragover');

				const files = event.dataTransfer?.files;
				if (!files || files.length === 0) {
					return;
				}

				const imageFile = Array.from(files).find((file) => file.type.startsWith('image/'));
				submitSelectedFile(imageFile);
			});
		});
	})();
</script>

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
