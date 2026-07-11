<?php

use App\Services\Seo\SeoMeta;
use Modules\Main\Template;

/** @var Template $this */

$seo = $this->getParam('seo');
if (!$seo instanceof SeoMeta) {
	return;
}

$escape = static fn(?string $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$robots = [];
$robots[] = $seo->index ? 'index' : 'noindex';
$robots[] = $seo->follow ? 'follow' : 'nofollow';
?>
<title><?= $escape($seo->title) ?></title>
<?php if ($seo->description !== ''): ?>
<meta name="description" content="<?= $escape($seo->description) ?>">
<?php endif; ?>
<meta name="robots" content="<?= $escape(implode(', ', $robots)) ?>">
<link rel="canonical" href="<?= $escape($seo->canonical) ?>">

<meta property="og:type" content="<?= $escape($seo->ogType) ?>">
<meta property="og:title" content="<?= $escape($seo->ogTitle) ?>">
<?php if ($seo->ogDescription !== ''): ?>
<meta property="og:description" content="<?= $escape($seo->ogDescription) ?>">
<?php endif; ?>
<meta property="og:url" content="<?= $escape($seo->canonical) ?>">
<?php if ($seo->ogImage !== null && $seo->ogImage !== ''): ?>
<meta property="og:image" content="<?= $escape($seo->ogImage) ?>">
<?php endif; ?>
<?php if ($seo->siteName !== ''): ?>
<meta property="og:site_name" content="<?= $escape($seo->siteName) ?>">
<?php endif; ?>
