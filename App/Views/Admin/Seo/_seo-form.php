<?php
$record = $record ?? null;
$resolved = $resolved ?? null;
$csrfToken = (string) ($csrfToken ?? '');

$title = (string) ($record->title ?? '');
$description = (string) ($record->description ?? '');
$canonicalUrl = (string) ($record->canonical_url ?? '');
$robotsIndex = $record === null ? true : (int) ($record->robots_index ?? 1) === 1;
$robotsFollow = $record === null ? true : (int) ($record->robots_follow ?? 1) === 1;
$ogTitle = (string) ($record->og_title ?? '');
$ogDescription = (string) ($record->og_description ?? '');
$ogImage = (string) ($record->og_image ?? '');
?>

<form action="<?= htmlspecialchars($action) ?>" method="post" enctype="multipart/form-data" class="seo-form" data-seo-form>
	<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

	<div class="row g-4">
		<div class="col-lg-7">
			<div class="mb-3">
				<label class="form-label" for="seo_title">SEO title</label>
				<input class="form-control" id="seo_title" name="title" type="text" maxlength="255" value="<?= htmlspecialchars($title) ?>" data-seo-title>
				<div class="form-text"><span data-seo-title-count>0</span> / 255</div>
			</div>

			<div class="mb-3">
				<label class="form-label" for="seo_description">Description</label>
				<textarea class="form-control" id="seo_description" name="description" rows="4" maxlength="320" data-seo-description><?= htmlspecialchars($description) ?></textarea>
				<div class="form-text"><span data-seo-description-count>0</span> / 320</div>
			</div>

			<div class="mb-3">
				<label class="form-label" for="seo_canonical">Canonical URL</label>
				<input class="form-control" id="seo_canonical" name="canonical_url" type="text" maxlength="500" value="<?= htmlspecialchars($canonicalUrl) ?>" placeholder="<?= htmlspecialchars((string) ($resolved->canonical ?? '')) ?>" data-seo-canonical>
			</div>

			<div class="row g-3 mb-3">
				<div class="col-sm-6">
					<div class="form-check">
						<input class="form-check-input" id="robots_index" name="robots_index" type="checkbox" <?= $robotsIndex ? 'checked' : '' ?> data-seo-index>
						<label class="form-check-label" for="robots_index">Разрешить индексацию</label>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-check">
						<input class="form-check-input" id="robots_follow" name="robots_follow" type="checkbox" <?= $robotsFollow ? 'checked' : '' ?> data-seo-follow>
						<label class="form-check-label" for="robots_follow">Разрешить переход по ссылкам</label>
					</div>
				</div>
			</div>

			<div class="mb-3">
				<label class="form-label" for="og_title">OG title</label>
				<input class="form-control" id="og_title" name="og_title" type="text" maxlength="255" value="<?= htmlspecialchars($ogTitle) ?>" data-seo-og-title>
			</div>

			<div class="mb-3">
				<label class="form-label" for="og_description">OG description</label>
				<textarea class="form-control" id="og_description" name="og_description" rows="3" maxlength="320" data-seo-og-description><?= htmlspecialchars($ogDescription) ?></textarea>
			</div>

			<div class="mb-3">
				<label class="form-label" for="og_image">OG image</label>
				<input type="hidden" name="og_image_existing" value="<?= htmlspecialchars($ogImage) ?>">
				<input class="form-control mb-2" id="og_image" name="og_image" type="text" maxlength="500" value="<?= htmlspecialchars($ogImage) ?>" placeholder="<?= htmlspecialchars((string) ($resolved->ogImage ?? '')) ?>" data-seo-og-image>
				<input class="form-control" name="og_image_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
				<div class="form-text">Можно указать URL или загрузить файл в /upload/images/seo/</div>
			</div>

			<button class="btn btn-primary" type="submit">Сохранить</button>
		</div>

		<div class="col-lg-5">
			<div class="card border-0 shadow-sm mb-3">
				<div class="card-body">
					<div class="small text-secondary mb-2">Предпросмотр в поиске</div>
					<div class="seo-preview-search">
						<div class="seo-preview-search__title" data-seo-preview-search-title><?= htmlspecialchars((string) ($resolved->title ?? '')) ?></div>
						<div class="seo-preview-search__url" data-seo-preview-search-url><?= htmlspecialchars((string) ($resolved->canonical ?? '')) ?></div>
						<div class="seo-preview-search__description" data-seo-preview-search-description><?= htmlspecialchars((string) ($resolved->description ?? '')) ?></div>
					</div>
				</div>
			</div>

			<div class="card border-0 shadow-sm">
				<div class="card-body">
					<div class="small text-secondary mb-2">Предпросмотр соцсетей</div>
					<div class="seo-preview-social border rounded overflow-hidden">
						<div class="seo-preview-social__image" data-seo-preview-social-image style="<?= !empty($resolved->ogImage) ? 'background-image:url(' . htmlspecialchars((string) $resolved->ogImage, ENT_QUOTES) . ')' : '' ?>"></div>
						<div class="p-3">
							<div class="seo-preview-social__site small text-secondary" data-seo-preview-social-site><?= htmlspecialchars((string) ($resolved->siteName ?? '')) ?></div>
							<div class="seo-preview-social__title fw-semibold" data-seo-preview-social-title><?= htmlspecialchars((string) ($resolved->ogTitle ?? '')) ?></div>
							<div class="seo-preview-social__description small text-secondary" data-seo-preview-social-description><?= htmlspecialchars((string) ($resolved->ogDescription ?? '')) ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
