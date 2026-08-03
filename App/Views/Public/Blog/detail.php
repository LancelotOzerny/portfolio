<?php
/* @var array $data */

$topic = $data['topic'] ?? null;
$article = $data['article'] ?? null;
$isAdmin = (bool) ($data['is_admin'] ?? false);
$editMode = (bool) ($data['edit_mode'] ?? false);
$csrfToken = (string) ($data['csrf_token'] ?? '');
$saveSuccess = (bool) ($data['save_success'] ?? false);
$saveError = trim((string) ($data['save_error'] ?? ''));
$canEditArticle = $isAdmin && $editMode && isset($article['id']);

$renderArticleContent = static function (string $rawContent): void {
	$rawContent = trim($rawContent);
	if ($rawContent === '') {
		return;
	}

	if ($rawContent !== strip_tags($rawContent)) {
		echo (new \App\Services\Blog\ArticleContentSanitizer())->sanitize($rawContent);
		return;
	}

	foreach (preg_split('/\R{2,}/', $rawContent) ?: [] as $paragraph) {
		$paragraph = trim((string) $paragraph);
		if ($paragraph === '') {
			continue;
		}

		echo '<p>' . nl2br(htmlspecialchars($paragraph)) . '</p>';
	}
};

if ($topic === null || $article === null) {
	?>
	<section class="light-page-hero">
		<div class="site-container light-page-hero__container">
			<a class="project-detail__back" href="/blog/">Назад в блог</a>
			<h1 class="page-title">Статья не найдена</h1>
			<p class="light-page-hero__text">Возможно, ссылка изменилась или тестовая статья была удалена.</p>
		</div>
	</section>
	<?php
	return;
}
?>

<section class="blog-detail-hero">
	<img src="<?= htmlspecialchars((string) $article['detail_image']) ?>" alt="<?= htmlspecialchars((string) $article['title']) ?>">
	<div class="blog-detail-hero__overlay" aria-hidden="true"></div>
	<div class="site-container blog-detail-hero__container">
		<nav class="blog-breadcrumbs" aria-label="Хлебные крошки">
			<a href="/">Главная</a>
			<span>/</span>
			<a href="/blog/">Блог</a>
			<span>/</span>
			<a href="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/"><?= htmlspecialchars((string) $topic['name']) ?></a>
			<span>/</span>
			<span><?= htmlspecialchars((string) $article['title']) ?></span>
		</nav>
		<h2><?= htmlspecialchars((string) $article['title']) ?></h2>
		<p><?= htmlspecialchars((string) $article['preview']) ?></p>
		<div class="blog-detail__meta">
			<span><?= htmlspecialchars((string) $article['date']) ?></span>
		</div>
	</div>
</section>

<section class="light-page-section blog-page blog-detail">
	<div class="site-container">
		<?php if ($saveSuccess): ?>
			<div class="blog-editor-alert blog-editor-alert_success">Изменения сохранены.</div>
		<?php endif; ?>

		<?php if ($saveError !== ''): ?>
			<div class="blog-editor-alert blog-editor-alert_error"><?= htmlspecialchars($saveError) ?></div>
		<?php endif; ?>

		<?php if ($canEditArticle): ?>
			<form class="blog-detail-editor" action="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/save/" method="post">
				<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
				<input id="blog-detail-editor-input" type="hidden" name="detail_text" value="<?= htmlspecialchars((string) ($article['detail_text'] ?? '')) ?>">
				<input id="blog-detail-editor-blocks" type="hidden" name="detail_blocks" value="">

				<div id="blog-detail-editor-area"></div>

				<div class="blog-detail-editor__actions">
					<a href="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/" class="blog-detail-editor__button blog-detail-editor__button_secondary">Отмена</a>
					<button type="submit" class="blog-detail-editor__button">Сохранить</button>
				</div>
			</form>
		<?php endif; ?>

		<?php if (!$canEditArticle): ?>
			<article class="blog-detail__content">
				<?php $renderArticleContent((string) ($article['detail_text'] ?? implode("\n\n", $article['content'] ?? []))); ?>
			</article>
		<?php endif; ?>
	</div>
</section>

<?php if ($canEditArticle): ?>
	<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.8/dist/editorjs.umd.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.8/dist/header.umd.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@2.10.3/dist/image.umd.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@2.7.6/dist/quote.umd.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@editorjs/code@2.9.3/dist/code.umd.min.js"></script>
	<script>
	document.addEventListener('DOMContentLoaded', () => {
		const editorRoot = document.getElementById('blog-detail-editor-area');
		const editorInput = document.getElementById('blog-detail-editor-input');
		const blocksInput = document.getElementById('blog-detail-editor-blocks');
		const form = editorRoot ? editorRoot.closest('form') : null;
		const initialHtml = <?= json_encode((string) ($article['detail_text'] ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
		const csrfToken = <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
		const uploadUrl = '/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/image/';
		const uploadFileUrl = '/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/file/';

		if (!window.EditorJS || !editorRoot || !editorInput || !blocksInput || !form) {
			return;
		}

		class InlineTagTool {
			static get isInline() {
				return true;
			}

			constructor({ api }) {
				this.api = api;
				this.button = null;
				this.tag = this.constructor.tag;
				this.title = this.constructor.title;
			}

			render() {
				this.button = document.createElement('button');
				this.button.type = 'button';
				this.button.classList.add(this.api.styles.inlineToolButton);
				this.button.innerHTML = this.constructor.icon;
				this.button.title = this.title;
				return this.button;
			}

			surround(range) {
				if (!range || range.collapsed) {
					return;
				}

				const wrapper = document.createElement(this.tag);
				wrapper.appendChild(range.extractContents());
				range.insertNode(wrapper);
				this.api.selection.expandToTag(wrapper);
			}

			checkState(selection) {
				if (!this.button) {
					return;
				}

				const active = Boolean(selection.anchorNode && selection.anchorNode.parentElement && selection.anchorNode.parentElement.closest(this.tag));
				this.button.classList.toggle(this.api.styles.inlineToolButtonActive, active);
			}
		}

		class UnderlineTool extends InlineTagTool {}
		UnderlineTool.tag = 'u';
		UnderlineTool.title = 'Подчеркнутый текст';
		UnderlineTool.icon = '<u>U</u>';

		class StrikeTool extends InlineTagTool {}
		StrikeTool.tag = 's';
		StrikeTool.title = 'Зачеркнутый текст';
		StrikeTool.icon = '<s>S</s>';

		class InlineColorTool {
			static get isInline() {
				return true;
			}

			constructor({ api, config }) {
				this.api = api;
				this.config = config || {};
				this.button = null;
			}

			render() {
				this.button = document.createElement('button');
				this.button.type = 'button';
				this.button.classList.add(this.api.styles.inlineToolButton);
				this.button.innerHTML = this.config.icon || 'A';
				this.button.title = this.config.title || 'Цвет';
				return this.button;
			}

			surround(range) {
				if (!range || range.collapsed) {
					return;
				}

				const color = window.prompt(this.config.prompt || 'Введите цвет, например #fa5374');
				if (!color) {
					return;
				}

				const safeColor = color.trim();
				if (!/^(#[0-9a-f]{3,8}|rgba?\([0-9\s,.%]+\)|[a-z]+)$/i.test(safeColor)) {
					alert('Некорректный цвет.');
					return;
				}

				const wrapper = document.createElement('span');
				wrapper.style[this.config.property || 'color'] = safeColor;
				wrapper.appendChild(range.extractContents());
				range.insertNode(wrapper);
				this.api.selection.expandToTag(wrapper);
			}
		}

		class FileBlockTool {
			static get toolbox() {
				return {
					title: 'Файл',
					icon: '<svg width="17" height="17" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" fill="none" stroke="currentColor" stroke-width="2"/></svg>'
				};
			}

			constructor({ data, config }) {
				this.data = data || {};
				this.config = config || {};
				this.wrapper = null;
			}

			render() {
				this.wrapper = document.createElement('div');
				this.wrapper.className = 'blog-editor-file-block';
				this.renderContent();
				return this.wrapper;
			}

			save() {
				return {
					url: this.data.url || '',
					name: this.data.name || '',
					title: this.data.title || '',
					extension: this.data.extension || ''
				};
			}

			renderContent() {
				if (!this.wrapper) {
					return;
				}

				this.wrapper.innerHTML = '';

				if (this.data.url) {
					const icon = document.createElement('span');
					icon.className = 'blog-editor-file-block__icon blog-editor-file-block__icon_' + this.getExtension();
					icon.innerHTML = this.getIconSvg(this.getExtension());
					this.wrapper.appendChild(icon);

					const content = document.createElement('div');
					content.className = 'blog-editor-file-block__content';

					const titleInput = document.createElement('input');
					titleInput.type = 'text';
					titleInput.value = this.data.title || this.data.name || '';
					titleInput.placeholder = 'Заголовок файла';
					titleInput.addEventListener('input', () => {
						this.data.title = titleInput.value;
						link.textContent = this.getDisplayTitle();
					});
					content.appendChild(titleInput);

					const link = document.createElement('a');
					link.href = this.data.url;
					link.target = '_blank';
					link.rel = 'noopener noreferrer';
					link.textContent = this.getDisplayTitle();
					content.appendChild(link);
					this.wrapper.appendChild(content);

					const replaceButton = document.createElement('button');
					replaceButton.type = 'button';
					replaceButton.textContent = 'Заменить файл';
					replaceButton.addEventListener('click', () => this.selectFile());
					this.wrapper.appendChild(replaceButton);
					return;
				}

				const button = document.createElement('button');
				button.type = 'button';
				button.textContent = 'Выбрать DOCX, TXT или PDF';
				button.addEventListener('click', () => this.selectFile());
				this.wrapper.appendChild(button);
			}

			getDisplayTitle() {
				return this.data.title || this.data.name || this.data.url || 'Файл';
			}

			getExtension() {
				const extension = String(this.data.extension || '').toLowerCase();
				if (['docx', 'pdf', 'txt'].includes(extension)) {
					return extension;
				}

				const urlExtension = String(this.data.url || '').split('.').pop().toLowerCase();
				return ['docx', 'pdf', 'txt'].includes(urlExtension) ? urlExtension : 'txt';
			}

			getIconSvg(extension) {
				const label = extension.toUpperCase();
				const color = extension === 'pdf' ? '#fa5374' : (extension === 'docx' ? '#51bce8' : '#74c390');
				return `<svg width="34" height="42" viewBox="0 0 34 42" aria-hidden="true" focusable="false">
					<path d="M4 1h17l9 9v27a4 4 0 0 1-4 4H4a4 4 0 0 1-4-4V5a4 4 0 0 1 4-4z" fill="#fff" stroke="${color}" stroke-width="2"/>
					<path d="M21 1v9h9" fill="none" stroke="${color}" stroke-width="2"/>
					<rect x="4" y="24" width="26" height="12" rx="3" fill="${color}"/>
					<text x="17" y="32.5" text-anchor="middle" fill="#fff" font-size="7" font-family="Arial, sans-serif" font-weight="700">${label}</text>
				</svg>`;
			}

			selectFile() {
				const input = document.createElement('input');
				input.type = 'file';
				input.accept = '.docx,.txt,.pdf,application/pdf,text/plain,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
				input.addEventListener('change', () => {
					const file = input.files && input.files[0] ? input.files[0] : null;
					if (file) {
						this.uploadFile(file);
					}
				});
				input.click();
			}

			async uploadFile(file) {
				const formData = new FormData();
				formData.append('_csrf', this.config.csrfToken || '');
				formData.append('file', file);

				const response = await fetch(this.config.uploadUrl || '', {
					method: 'POST',
					body: formData,
					credentials: 'same-origin'
				});
				const result = await response.json();

				if (!result.success || !result.file || !result.file.url) {
					alert(result.error || 'Не удалось загрузить файл.');
					return;
				}

				this.data = {
					url: result.file.url,
					name: result.file.name || file.name,
					title: this.data.title || result.file.name || file.name,
					extension: result.file.extension || ''
				};
				this.renderContent();
			}
		}

		const escapeHtml = (value) => String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');

		const sanitizeInlineHtml = (html) => {
			const template = document.createElement('template');
			template.innerHTML = String(html || '');
			const allowedTags = new Set(['A', 'B', 'BR', 'CODE', 'I', 'MARK', 'S', 'SPAN', 'STRONG', 'U', 'EM']);

			template.content.querySelectorAll('*').forEach((element) => {
				if (!allowedTags.has(element.tagName)) {
					element.replaceWith(...element.childNodes);
					return;
				}

				Array.from(element.attributes).forEach((attribute) => {
					if (element.tagName === 'A' && ['href', 'target', 'rel'].includes(attribute.name)) {
						return;
					}

					if (element.tagName === 'SPAN' && attribute.name === 'style') {
						const color = element.style.color;
						const backgroundColor = element.style.backgroundColor;
						element.removeAttribute('style');
						if (color) {
							element.style.color = color;
						}
						if (backgroundColor) {
							element.style.backgroundColor = backgroundColor;
						}
						return;
					}

					element.removeAttribute(attribute.name);
				});
			});

			template.content.querySelectorAll('a').forEach((link) => {
				const href = (link.getAttribute('href') || '').trim();
				if (!href || /^javascript:/i.test(href)) {
					link.removeAttribute('href');
					return;
				}

				if (link.getAttribute('target') === '_blank') {
					link.setAttribute('rel', 'noopener noreferrer');
				}
			});

			return template.innerHTML;
		};

		const htmlToBlocks = (html) => {
			const rawHtml = String(html || '').trim();
			if (rawHtml && !/<[a-z!/][\s\S]*>/i.test(rawHtml)) {
				return rawHtml.split(/\n{2,}/).map((paragraph) => ({
					type: 'paragraph',
					data: {
						text: escapeHtml(paragraph.trim()).replace(/\n/g, '<br>')
					}
				}));
			}

			const template = document.createElement('template');
			template.innerHTML = rawHtml;
			const blocks = [];

			Array.from(template.content.children).forEach((element) => {
				const tagName = element.tagName.toLowerCase();

				if (/^h[1-6]$/.test(tagName)) {
					blocks.push({
						type: 'header',
						data: {
							text: element.innerHTML,
							level: Number(tagName.slice(1))
						}
					});
					return;
				}

				if (tagName === 'img') {
					blocks.push({
						type: 'image',
						data: {
							file: { url: element.getAttribute('src') || '' },
							caption: element.getAttribute('alt') || '',
							withBorder: false,
							withBackground: false,
							stretched: false
						}
					});
					return;
				}

				if (tagName === 'blockquote') {
					blocks.push({
						type: 'quote',
						data: {
							text: element.innerHTML,
							caption: '',
							alignment: 'left'
						}
					});
					return;
				}

				if (tagName === 'pre') {
					blocks.push({
						type: 'code',
						data: {
							code: element.textContent || ''
						}
					});
					return;
				}

				if (tagName === 'p') {
					const children = Array.from(element.children);
					const link = children.length === 1 && children[0].tagName.toLowerCase() === 'a' ? children[0] : null;
					const href = link ? link.getAttribute('href') || '' : '';
					if (href.startsWith('/upload/articles/') && /\.(docx|pdf|txt)$/i.test(href)) {
						blocks.push({
							type: 'file',
							data: {
								url: href,
								name: href.split('/').pop() || href,
								title: link.textContent || href.split('/').pop() || href,
								extension: href.split('.').pop() || ''
							}
						});
						return;
					}
				}

				blocks.push({
					type: 'paragraph',
					data: {
						text: element.innerHTML || element.textContent || ''
					}
				});
			});

			return blocks.length > 0 ? blocks : [{ type: 'paragraph', data: { text: '' } }];
		};

		const blocksToHtml = (blocks) => blocks.map((block) => {
			const data = block.data || {};

			if (block.type === 'header') {
				const level = [1, 2, 3, 4, 5, 6].includes(Number(data.level)) ? Number(data.level) : 2;
				return `<h${level}>${sanitizeInlineHtml(data.text)}</h${level}>`;
			}

			if (block.type === 'image') {
				const url = data.file && data.file.url ? String(data.file.url) : '';
				if (!url) {
					return '';
				}
				return `<img src="${escapeHtml(url)}" alt="${escapeHtml(data.caption || '')}">`;
			}

			if (block.type === 'quote') {
				return `<blockquote>${sanitizeInlineHtml(data.text)}</blockquote>`;
			}

			if (block.type === 'code') {
				return `<pre><code>${escapeHtml(data.code || '')}</code></pre>`;
			}

			if (block.type === 'file') {
				const url = data.url ? String(data.url) : '';
				if (!url) {
					return '';
				}
				const title = data.title || data.name || url.split('/').pop() || url;
				return `<p><a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(title)}</a></p>`;
			}

			return `<p>${sanitizeInlineHtml(data.text)}</p>`;
		}).filter(Boolean).join("\n");

		const editor = new EditorJS({
			holder: editorRoot,
			data: {
				blocks: htmlToBlocks(initialHtml)
			},
			inlineToolbar: ['bold', 'italic', 'link', 'underline', 'strike', 'textColor', 'markerColor'],
			tools: {
				header: {
					class: Header,
					inlineToolbar: ['bold', 'italic', 'link', 'underline', 'strike', 'textColor', 'markerColor'],
					config: {
						levels: [1, 2, 3, 4, 5, 6],
						defaultLevel: 2
					}
				},
				image: {
					class: ImageTool,
					config: {
						uploader: {
							uploadByFile: async (file) => {
								const formData = new FormData();
								formData.append('_csrf', csrfToken);
								formData.append('image', file);

								const response = await fetch(uploadUrl, {
									method: 'POST',
									body: formData,
									credentials: 'same-origin'
								});

								return response.json();
							}
						}
					}
				},
				quote: {
					class: Quote,
					inlineToolbar: ['bold', 'italic', 'link', 'underline', 'strike', 'textColor', 'markerColor']
				},
				code: CodeTool,
				file: {
					class: FileBlockTool,
					config: {
						uploadUrl: uploadFileUrl,
						csrfToken: csrfToken
					}
				},
				underline: UnderlineTool,
				strike: StrikeTool,
				textColor: {
					class: InlineColorTool,
					config: {
						title: 'Цвет текста',
						icon: 'A',
						property: 'color',
						prompt: 'Введите цвет текста, например #222222'
					}
				},
				markerColor: {
					class: InlineColorTool,
					config: {
						title: 'Выделение цветом',
						icon: '▰',
						property: 'backgroundColor',
						prompt: 'Введите цвет выделения, например #fff3a3'
					}
				}
			}
		});

		form.addEventListener('submit', async (event) => {
			event.preventDefault();

			const output = await editor.save();
			blocksInput.value = JSON.stringify(output);
			editorInput.value = blocksToHtml(output.blocks || []);
			form.submit();
		});
	});
	</script>
<?php endif; ?>
