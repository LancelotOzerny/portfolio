<?php
$isEdit = isset($item) && $item !== null;
$roleName = (string) ($item->role ?? '');
$roleCode = (string) ($item->code ?? '');
$roleLevel = (string) ($item->level ?? '0');
$submitLabel = $isEdit ? 'Сохранить' : 'Добавить';
?>

<form action="<?= htmlspecialchars($action) ?>" method="post" class="role-form" data-role-form>
	<div class="row g-3">
		<div class="col-md-6">
			<label class="form-label" for="role_name">Название</label>
			<input class="form-control" id="role_name" name="role" type="text" required maxlength="255" value="<?= htmlspecialchars($roleName) ?>">
		</div>
		<div class="col-md-6">
			<label class="form-label" for="role_code">Код</label>
			<input
				class="form-control font-monospace"
				id="role_code"
				name="code"
				type="text"
				maxlength="64"
				pattern="[A-Za-z0-9_-]+"
				title="Только латинские буквы, цифры, - и _"
				value="<?= htmlspecialchars($roleCode) ?>"
				<?= $isEdit ? 'required' : '' ?>
			>
			<div class="form-text">Латинские буквы, цифры, "-" и "_". Если не указать, заполнится из названия.</div>
		</div>
		<div class="col-md-4">
			<label class="form-label" for="role_level">Уровень прав</label>
			<input class="form-control" id="role_level" name="level" type="number" required min="0" max="1000" step="1" value="<?= htmlspecialchars($roleLevel) ?>">
			<div class="form-text">Доступ в админку: от 100.</div>
		</div>
	</div>

	<div class="mt-3">
		<button class="btn btn-primary" type="submit"><?= htmlspecialchars($submitLabel) ?></button>
	</div>
</form>

<script>
(function () {
	const form = document.currentScript ? document.currentScript.previousElementSibling : null;
	if (!form || !form.matches('[data-role-form]')) {
		return;
	}

	const map = {а:'a',б:'b',в:'v',г:'g',д:'d',е:'e',ё:'yo',ж:'zh',з:'z',и:'i',й:'y',к:'k',л:'l',м:'m',н:'n',о:'o',п:'p',р:'r',с:'s',т:'t',у:'u',ф:'f',х:'h',ц:'ts',ч:'ch',ш:'sh',щ:'sch',ъ:'',ы:'y',ь:'',э:'e',ю:'yu',я:'ya'};
	const toCode = (value) => String(value || '')
		.split('')
		.map((char) => map[char.toLowerCase()] ?? char)
		.join('')
		.toLowerCase()
		.replace(/[^a-z0-9_-]+/g, '-')
		.replace(/-+/g, '-')
		.replace(/^[-_]+|[-_]+$/g, '');

	const nameInput = form.querySelector('#role_name');
	const codeInput = form.querySelector('#role_code');
	let codeLocked = Boolean(codeInput && codeInput.value.trim() !== '');

	if (!nameInput || !codeInput) {
		return;
	}

	nameInput.addEventListener('input', () => {
		if (!codeLocked) {
			codeInput.value = toCode(nameInput.value);
		}
	});

	codeInput.addEventListener('input', () => {
		codeLocked = true;
		codeInput.value = toCode(codeInput.value);
	});
})();
</script>
