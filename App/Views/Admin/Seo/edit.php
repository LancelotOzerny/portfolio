<?php
/* @var array $data */

use App\Services\Seo\Config\SeoConfig;

$pageKey = (string) ($data['pageKey'] ?? '');
$pageConfig = is_array($data['pageConfig'] ?? null) ? $data['pageConfig'] : [];
$record = $data['record'] ?? null;
$resolved = $data['resolved'] ?? null;
$csrfToken = (string) ($data['csrfToken'] ?? '');
$saved = (bool) ($data['saved'] ?? false);
$error = trim((string) ($data['error'] ?? ''));
$domain = rtrim((string) ((new SeoConfig())->getSite()['domain'] ?? ''), '/');
?>

<link rel="stylesheet" href="/Templates/Admin/styles/seo.css">

<section class="admin-seo-edit"
	data-seo-editor
	data-site-name="<?= htmlspecialchars((string) ($resolved->siteName ?? ''), ENT_QUOTES) ?>"
	data-default-title="<?= htmlspecialchars((string) ($pageConfig['title'] ?? ''), ENT_QUOTES) ?>"
	data-default-description="<?= htmlspecialchars((string) ($pageConfig['description'] ?? ''), ENT_QUOTES) ?>"
	data-page-path="<?= htmlspecialchars((string) ($pageConfig['path'] ?? '/'), ENT_QUOTES) ?>"
	data-domain="<?= htmlspecialchars($domain, ENT_QUOTES) ?>"
	data-default-og-image="<?= htmlspecialchars((string) ($resolved->ogImage ?? ''), ENT_QUOTES) ?>">
	<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
		<div>
			<div class="small text-secondary mb-1">SEO</div>
			<h1 class="h3 mb-0"><?= htmlspecialchars((string) ($pageConfig['label'] ?? $pageKey)) ?></h1>
			<div class="small text-secondary"><code><?= htmlspecialchars((string) ($pageConfig['path'] ?? '/')) ?></code></div>
		</div>
		<a class="btn btn-outline-secondary" href="/admin/seo/">К списку</a>
	</div>

	<?php if ($saved): ?>
		<div class="alert alert-success">SEO-настройки сохранены.</div>
	<?php endif; ?>
	<?php if ($error !== ''): ?>
		<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
	<?php endif; ?>

	<?php
	$action = '/admin/seo/' . rawurlencode($pageKey) . '/';
	include __DIR__ . '/_seo-form.php';
	?>

	<div class="card border-0 shadow-sm mt-4">
		<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
			<div>
				<div class="fw-semibold">Сбросить ручные настройки</div>
				<div class="small text-secondary">Страница снова будет использовать значения из SEO-конфига.</div>
			</div>
			<form action="/admin/seo/<?= rawurlencode($pageKey) ?>/reset/" method="post" onsubmit="return confirm('Сбросить ручные SEO-настройки для этой страницы?');">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
				<button class="btn btn-outline-danger" type="submit">Сбросить ручные настройки</button>
			</form>
		</div>
	</div>
</section>

<script src="/Templates/Admin/scripts/seo.js"></script>
