<footer class="site-footer scroll-show-area">
	<div class="container site-footer__container">
		<div class="site-footer__brand">
			<a class="site-footer__logo" href="/">LANCY</a>
			<p class="site-footer__description">
				&#1057;&#1086;&#1079;&#1076;&#1072;&#1102; &#1089;&#1086;&#1074;&#1088;&#1077;&#1084;&#1077;&#1085;&#1085;&#1099;&#1077; &#1074;&#1077;&#1073;-&#1088;&#1077;&#1096;&#1077;&#1085;&#1080;&#1103; &#1076;&#1083;&#1103; &#1074;&#1072;&#1096;&#1077;&#1075;&#1086; &#1073;&#1080;&#1079;&#1085;&#1077;&#1089;&#1072; &#1089; &#1080;&#1089;&#1087;&#1086;&#1083;&#1100;&#1079;&#1086;&#1074;&#1072;&#1085;&#1080;&#1077;&#1084; PHP, MySQL, HTML, Bootstrap &#1080; jQuery.
			</p>
		</div>

		<nav class="site-footer__nav" aria-label="Sitemap">
			<?php
			(new \Components\Navigation\Navigation([
				'type' => 'Main',
				'template' => 'Column'
			]))->render();
			?>
		</nav>

		<div class="site-footer__contacts">
			<div class="site-footer__title">&#1050;&#1086;&#1085;&#1090;&#1072;&#1082;&#1090;&#1099;</div>
			<div class="site-footer__contact-text">&#1051;&#1080;&#1087;&#1077;&#1094;&#1082;, &#1056;&#1086;&#1089;&#1089;&#1080;&#1103;</div>
			<a class="site-footer__contact-link" href="tel:+79205201831">8 (920) 520 18 31</a>
			<a class="site-footer__contact-link" href="mailto:lancelot.ozernuy@gmail.com">lancelot.ozernuy@gmail.com</a>
		</div>
	</div>

	<div class="site-footer__copyright">
		&copy; 2026 LANCY. &#1042;&#1089;&#1077; &#1087;&#1088;&#1072;&#1074;&#1072; &#1079;&#1072;&#1097;&#1080;&#1097;&#1077;&#1085;&#1099;.
	</div>
</footer>

<script src="/Templates/Main/script.js"></script>
</body>
</html>
