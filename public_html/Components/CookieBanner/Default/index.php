<?php
if ($this->getParam('already_decided')) {
	return;
}

$cooldownDays = max(1, (int) $this->getParam('cooldown_days'));
$policyPath = (string) $this->getParam('policy_path');
$cookieName = (string) $this->getParam('cookie_name');
$storageKey = (string) $this->getParam('storage_key');
$textBefore = (string) $this->getParam('text_before');
$policyLinkText = (string) $this->getParam('policy_link_text');
$textAfter = (string) $this->getParam('text_after');
$acceptText = (string) $this->getParam('accept_text');
$declineText = (string) $this->getParam('decline_text');
$bannerId = 'cookie-banner';
?>
<div
	id="<?= htmlspecialchars($bannerId) ?>"
	class="cookie-banner"
	data-cookie-banner
	data-cookie-name="<?= htmlspecialchars($cookieName) ?>"
	data-storage-key="<?= htmlspecialchars($storageKey) ?>"
	data-cooldown-days="<?= $cooldownDays ?>"
	role="dialog"
	aria-live="polite"
	aria-label="Уведомление об использовании cookie"
>
	<div class="site-container cookie-banner__container">
		<p class="cookie-banner__text">
			<?= htmlspecialchars($textBefore) ?><a class="cookie-banner__link" href="<?= htmlspecialchars($policyPath) ?>"><?= htmlspecialchars($policyLinkText) ?></a><?= htmlspecialchars($textAfter) ?>
		</p>
		<div class="cookie-banner__actions">
			<button class="cookie-banner__button" type="button" data-cookie-banner-accept>
				<?= htmlspecialchars($acceptText) ?>
			</button>
			<button class="cookie-banner__button" type="button" data-cookie-banner-decline>
				<?= htmlspecialchars($declineText) ?>
			</button>
		</div>
	</div>
</div>
<script>
(function () {
	var banner = document.getElementById(<?= json_encode($bannerId, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
	if (!banner) {
		return;
	}

	var cookieName = banner.getAttribute('data-cookie-name') || 'ls_cookie_consent';
	var storageKey = banner.getAttribute('data-storage-key') || cookieName;
	var hasChoice = false;

	try {
		var stored = window.localStorage.getItem(storageKey);
		if (stored === 'accepted' || stored === 'declined' || stored === '1') {
			hasChoice = true;
		}
	} catch (error) {}

	if (!hasChoice && document.cookie.indexOf(cookieName + '=') !== -1) {
		hasChoice = true;
	}

	if (hasChoice) {
		banner.hidden = true;
	}
})();
</script>
