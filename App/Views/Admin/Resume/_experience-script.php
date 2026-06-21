<script>
document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.experience-form').forEach((form) => {
		const currentToggle = form.querySelector('.experience-current-toggle');
		const endDate = form.querySelector('.experience-end-date');

		const syncEndDate = () => {
			if (!currentToggle || !endDate) return;
			endDate.disabled = currentToggle.checked;
			endDate.required = !currentToggle.checked;
		};

		currentToggle?.addEventListener('change', syncEndDate);
		syncEndDate();

		form.querySelectorAll('[data-command]').forEach((button) => {
			button.addEventListener('mousedown', (event) => event.preventDefault());
			button.addEventListener('click', () => {
				const editor = form.querySelector('.rich-editor');
				if (!editor) return;
				editor.focus();
				document.execCommand(button.dataset.command, false);
			});
		});

		form.addEventListener('submit', () => {
			form.querySelectorAll('.rich-editor-input').forEach((input) => {
				const editor = document.getElementById(input.dataset.editorId || '');
				input.value = editor ? editor.innerHTML : '';
			});
		});
	});
});
</script>
