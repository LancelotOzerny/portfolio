<?php
/**
 * Вкладка SEO в формах статьи/рубрики.
 *
 * @var array $seoForm
 * @var string $previewTitle
 * @var string $previewDescription
 * @var string $previewImage
 */

$seoForm = is_array($seoForm ?? null) ? $seoForm : [];
$seoTitle = (string) ($seoForm['title'] ?? '');
$seoDescription = (string) ($seoForm['description'] ?? '');
$seoKeywords = (string) ($seoForm['keywords'] ?? '');
$seoRobotsIndex = array_key_exists('robots_index', $seoForm) ? (bool) $seoForm['robots_index'] : true;
$seoRobotsFollow = array_key_exists('robots_follow', $seoForm) ? (bool) $seoForm['robots_follow'] : true;
$previewTitle = trim((string) ($previewTitle ?? ''));
$previewDescription = trim((string) ($previewDescription ?? ''));
$previewImage = trim((string) ($previewImage ?? ''));
?>

<div class="row g-4">
	<div class="col-lg-7">
		<div class="mb-3">
			<label class="form-label" for="seo_title">Заголовок в браузере</label>
			<input
				class="form-control"
				id="seo_title"
				name="seo_title"
				type="text"
				maxlength="255"
				value="<?= htmlspecialchars($seoTitle) ?>"
				placeholder="<?= htmlspecialchars($previewTitle) ?>"
			>
			<div class="form-text">Если пусто — используется название из основной информации.</div>
		</div>

		<div class="mb-3">
			<label class="form-label" for="seo_description">SEO описание</label>
			<textarea
				class="form-control"
				id="seo_description"
				name="seo_description"
				rows="4"
				maxlength="320"
				placeholder="<?= htmlspecialchars($previewDescription) ?>"
			><?= htmlspecialchars($seoDescription) ?></textarea>
			<div class="form-text">До 320 символов. Если пусто — используется текст preview.</div>
		</div>

		<div class="mb-3">
			<label class="form-label" for="seo_keywords">Ключевые слова</label>
			<input
				class="form-control"
				id="seo_keywords"
				name="seo_keywords"
				type="text"
				maxlength="500"
				value="<?= htmlspecialchars($seoKeywords) ?>"
				placeholder="слово1, слово2, слово3"
			>
		</div>

		<div class="row g-3">
			<div class="col-sm-6">
				<div class="form-check">
					<input class="form-check-input" id="seo_robots_index" name="seo_robots_index" type="checkbox" <?= $seoRobotsIndex ? 'checked' : '' ?>>
					<label class="form-check-label" for="seo_robots_index">Разрешить индексацию</label>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="form-check">
					<input class="form-check-input" id="seo_robots_follow" name="seo_robots_follow" type="checkbox" <?= $seoRobotsFollow ? 'checked' : '' ?>>
					<label class="form-check-label" for="seo_robots_follow">Разрешить переход по ссылкам</label>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="alert alert-light border mb-3">
			<div class="fw-semibold mb-1">Посты в соцсетях</div>
			<div class="small text-secondary mb-0">
				Для Open Graph используются название, описание и картинка из вкладки Preview.
			</div>
		</div>

		<div class="card border-0 shadow-sm">
			<div class="card-body">
				<div class="small text-secondary mb-2">Предпросмотр соцсетей</div>
				<div class="border rounded overflow-hidden bg-white">
					<?php if ($previewImage !== ''): ?>
						<div class="bg-light" style="height: 160px; background: center / cover no-repeat url('<?= htmlspecialchars($previewImage, ENT_QUOTES) ?>');"></div>
					<?php else: ?>
						<div class="bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 160px;">Нет preview-изображения</div>
					<?php endif; ?>
					<div class="p-3">
						<div class="fw-semibold"><?= htmlspecialchars($previewTitle !== '' ? $previewTitle : 'Название') ?></div>
						<div class="small text-secondary mt-1"><?= htmlspecialchars($previewDescription !== '' ? $previewDescription : 'Описание из preview') ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
