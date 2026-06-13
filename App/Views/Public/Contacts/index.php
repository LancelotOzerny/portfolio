<?php
\Modules\Main\AssetLoader::getInstance()->addJs('/Templates/Inner/scripts/feedback.js');
?>

<div class="container contacts-page">
	<section class="contacts-shell scroll-show-area">
		<div class="contacts-shell__intro">
			<h2>&#1057;&#1074;&#1103;&#1078;&#1080;&#1090;&#1077;&#1089;&#1100; &#1089;&#1086; &#1084;&#1085;&#1086;&#1081;</h2>
			<p>&#1054;&#1087;&#1080;&#1096;&#1080;&#1090;&#1077; &#1079;&#1072;&#1076;&#1072;&#1095;&#1091;, &#1089;&#1088;&#1086;&#1082;&#1080; &#1080; &#1086;&#1078;&#1080;&#1076;&#1072;&#1085;&#1080;&#1103;. &#1071; &#1074;&#1077;&#1088;&#1085;&#1091;&#1089;&#1100; &#1089; &#1087;&#1086;&#1085;&#1103;&#1090;&#1085;&#1099;&#1084;&#1080; &#1074;&#1086;&#1087;&#1088;&#1086;&#1089;&#1072;&#1084;&#1080; &#1080;&#1083;&#1080; &#1087;&#1088;&#1077;&#1076;&#1083;&#1086;&#1078;&#1077;&#1085;&#1080;&#1077;&#1084;.</p>
		</div>

		<form id="message-form" class="contacts-form">
			<div class="contacts-form__row">
				<div class="contacts-form__field">
					<label for="name" class="form-label">&#1042;&#1072;&#1096;&#1077; &#1080;&#1084;&#1103;</label>
					<input type="text" class="form-control" id="name" placeholder="&#1050;&#1072;&#1082; &#1082; &#1074;&#1072;&#1084; &#1086;&#1073;&#1088;&#1072;&#1097;&#1072;&#1090;&#1100;&#1089;&#1103;" required>
				</div>

				<div class="contacts-form__field">
					<label for="email" class="form-label">Email</label>
					<input type="email" class="form-control" id="email" placeholder="example@example.com" required>
				</div>
			</div>

			<div class="contacts-form__field">
				<label for="message" class="form-label">&#1057;&#1086;&#1086;&#1073;&#1097;&#1077;&#1085;&#1080;&#1077;</label>
				<textarea class="form-control" id="message" rows="6" placeholder="&#1056;&#1072;&#1089;&#1089;&#1082;&#1072;&#1078;&#1080;&#1090;&#1077; &#1086; &#1087;&#1088;&#1086;&#1077;&#1082;&#1090;&#1077;, &#1089;&#1072;&#1081;&#1090;&#1077; &#1080;&#1083;&#1080; &#1076;&#1086;&#1088;&#1072;&#1073;&#1086;&#1090;&#1082;&#1077;..." required></textarea>
			</div>

			<button type="submit" class="btn contacts-form__submit">&#1054;&#1090;&#1087;&#1088;&#1072;&#1074;&#1080;&#1090;&#1100; &#1089;&#1086;&#1086;&#1073;&#1097;&#1077;&#1085;&#1080;&#1077;</button>
		</form>
	</section>

	<aside class="contacts-aside scroll-show-area">
		<div class="contacts-aside__card">
			<h3>&#1050;&#1086;&#1085;&#1090;&#1072;&#1082;&#1090;&#1099;</h3>
			<p>&#1051;&#1080;&#1087;&#1077;&#1094;&#1082;, &#1056;&#1086;&#1089;&#1089;&#1080;&#1103;</p>
			<a href="tel:89205201831">8 (920) 520 18 31</a>
			<a href="mailto:lancelot.ozernuy@gmail.com">lancelot.ozernuy@gmail.com</a>
		</div>

		<div class="contacts-aside__links">
			<a href="https://github.com/LancelotOzerny/">GitHub</a>
			<a href="https://t.me/lalalancy">Telegram</a>
			<a href="https://vk.com/lancy2002">VK</a>
		</div>
	</aside>
</div>

<div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
	<div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="toast-header">
			<strong class="me-auto toast-title">&#1059;&#1074;&#1077;&#1076;&#1086;&#1084;&#1083;&#1077;&#1085;&#1080;&#1077;</strong>
			<small class="toast-time">&#1057;&#1077;&#1081;&#1095;&#1072;&#1089;</small>
			<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
		</div>
		<div class="toast-body"></div>
	</div>
</div>
