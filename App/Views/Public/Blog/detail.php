<?php
/* @var array $data */

$topic = $data['topic'] ?? null;
$article = $data['article'] ?? null;
$isAdmin = (bool) ($data['is_admin'] ?? false);
$editMode = (bool) ($data['edit_mode'] ?? false);
$csrfToken = (string) ($data['csrf_token'] ?? '');
$saveSuccess = (bool) ($data['save_success'] ?? false);
$saveError = trim((string) ($data['save_error'] ?? ''));
$flash = is_array($data['flash'] ?? null) ? $data['flash'] : null;
$rating = is_array($data['rating'] ?? null) ? $data['rating'] : [];
$ratingAverage = (float) ($rating['average'] ?? 0);
$ratingCount = (int) ($rating['count'] ?? 0);
$userRating = isset($rating['user_rating']) && $rating['user_rating'] !== null ? (int) $rating['user_rating'] : null;
$widgetCatalog = new \App\Services\Blog\WidgetCatalog();

$renderStarsMarkup = static function (float $value, int $max = 5): string {
	$filled = (int) max(0, min($max, round($value)));
	$markup = '';

	for ($i = 1; $i <= $max; $i++) {
		$markup .= '<span class="blog-rating__star' . ($i <= $filled ? ' is-active' : '') . '" aria-hidden="true">★</span>';
	}

	return $markup;
};

$renderArticleContent = static function (string $rawContent) use ($widgetCatalog): void {
	$rawContent = trim($rawContent);
	if ($rawContent === '') {
		return;
	}

	if ($rawContent !== strip_tags($rawContent)) {
		$html = (new \App\Services\Blog\ArticleContentSanitizer())->sanitize($rawContent);
		echo (new \App\Services\Blog\ArticleWidgetRenderer($widgetCatalog))->hydrate($html);
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

$canEditArticle = $isAdmin && $editMode && isset($article['id']);
$comments = is_array($data['comments'] ?? null) ? $data['comments'] : [];
$seoForm = is_array($data['seo_form'] ?? null) ? $data['seo_form'] : [];
?>

<section class="blog-detail-hero">
	<img src="<?= htmlspecialchars((string) $article['detail_image']) ?>" alt="<?= htmlspecialchars((string) $article['title']) ?>" loading="eager" decoding="async" fetchpriority="high">
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
		<div class="blog-detail-hero__title-block">
			<h1><?= htmlspecialchars((string) $article['title']) ?></h1>
			<div class="blog-rating" data-blog-rating-summary>
				<span class="blog-rating__stars" data-blog-rating-stars><?= $renderStarsMarkup($ratingAverage) ?></span>
				<span class="blog-rating__value" data-blog-rating-value><?= number_format($ratingAverage, 1, '.', '') ?></span>
			</div>
		</div>
		<p><?= htmlspecialchars((string) $article['preview']) ?></p>
		<div class="blog-detail__meta">
			<span><?= htmlspecialchars((string) $article['date']) ?></span>
		</div>
	</div>
</section>

<?php if ($canEditArticle): ?>
	<?php
	$basicAction = '/blog/' . rawurlencode((string) $topic['slug']) . '/' . rawurlencode((string) $article['slug']) . '/settings/basic/';
	$seoAction = '/blog/' . rawurlencode((string) $topic['slug']) . '/' . rawurlencode((string) $article['slug']) . '/settings/seo/';
	$basicTitle = (string) ($article['title'] ?? '');
	$basicDescription = (string) ($article['preview'] ?? '');
	$seoTitle = (string) ($seoForm['title'] ?? '');
	$seoDescription = (string) ($seoForm['description'] ?? '');
	$seoKeywords = (string) ($seoForm['keywords'] ?? '');
	$basicTitleLabel = 'Название статьи';
	$basicDescriptionLabel = 'Описание';
	include __DIR__ . '/_settings-modals.php';

	$publicationService = new \App\Services\Blog\BlogArticlePublicationService();
	$dbArticle = null;
	try {
		$dbArticle = (new \Models\BlogArticlesModel())->findById((int) ($article['id'] ?? 0));
	} catch (\Throwable) {
	}
	if ($dbArticle !== null && !$publicationService->isPublished($dbArticle)) {
		$articleId = (int) ($dbArticle->id ?? 0);
		$scheduledDatetime = $publicationService->getScheduledDatetime($dbArticle);
		$scheduleInputValue = $publicationService->formatForInput(
			$scheduledDatetime !== null ? $scheduledDatetime : date('Y-m-d H:i:s', strtotime('+1 hour'))
		);
		$backUrl = '/blog/' . rawurlencode((string) $topic['slug']) . '/' . rawurlencode((string) $article['slug']) . '/';
		include __DIR__ . '/_publication-modal.php';
	}
	?>
<?php endif; ?>

<section class="light-page-section blog-page blog-detail">
	<div class="site-container">
		<?php if ($flash !== null): ?>
			<div class="blog-editor-alert <?= !empty($flash['success']) ? 'blog-editor-alert_success' : 'blog-editor-alert_error' ?>">
				<?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
			</div>
		<?php endif; ?>

		<?php if ($saveSuccess): ?>
			<div class="blog-editor-alert blog-editor-alert_success">Изменения сохранены.</div>
		<?php endif; ?>

		<?php if ($saveError !== ''): ?>
			<div class="blog-editor-alert blog-editor-alert_error"><?= htmlspecialchars($saveError) ?></div>
		<?php endif; ?>

		<?php if ($canEditArticle): ?>
			<form class="blog-detail-editor content-editor" action="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/save/" method="post">
				<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
				<?php
				(new \App\Services\ContentEditor\ContentEditor())->render([
					'id' => 'blog-detail-editor',
					'name' => 'detail_text',
					'html' => (string) ($article['detail_text'] ?? ''),
					'wrap' => false,
					'uploadUrl' => '/blog/' . (string) $topic['slug'] . '/' . (string) $article['slug'] . '/image/',
					'uploadFileUrl' => '/blog/' . (string) $topic['slug'] . '/' . (string) $article['slug'] . '/file/',
				]);
				?>

				<div class="blog-detail-editor__actions">
					<a href="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/" class="blog-detail-editor__button blog-detail-editor__button_secondary">Отмена</a>
					<button type="submit" class="blog-detail-editor__button">Сохранить</button>
				</div>
			</form>
		<?php endif; ?>

		<?php if (!$canEditArticle): ?>
			<article class="blog-detail__content">
				<?php $renderArticleContent((string) ($article['detail_text'] ?? implode("\n\n", $article['content'] ?? []))); ?>

				<?php if (isset($article['id'])): ?>
					<?php $voteValue = $userRating ?? 0; ?>
					<section
						class="blog-article-vote"
						data-blog-vote
						data-user-rating="<?= (int) $voteValue ?>"
						data-rate-url="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/rate/"
						aria-label="Оценка статьи"
					>
						<div class="blog-article-vote__stars" style="padding-top: 25px" role="radiogroup" aria-label="Рейтинг от 1 до 5">
							<?php for ($star = 1; $star <= 5; $star++): ?>
								<button
									type="button"
									class="blog-article-vote__star<?= $star <= $voteValue ? ' is-active' : '' ?>"
									data-rating="<?= $star ?>"
									aria-label="<?= $star ?> из 5"
								>★</button>
							<?php endfor; ?>
						</div>
						<div class="blog-article-vote__message" data-blog-vote-message>
							<?php if ($userRating !== null): ?>
								Ваша оценка: <?= (int) $userRating ?> из 5
							<?php elseif ($ratingCount > 0): ?>
								Средняя оценка: <?= number_format($ratingAverage, 1, '.', '') ?> (<?= $ratingCount ?>)
							<?php else: ?>
								Поставьте оценку от 1 до 5
							<?php endif; ?>
						</div>
						<input type="hidden" data-blog-vote-csrf value="<?= htmlspecialchars($csrfToken) ?>">
					</section>
				<?php endif; ?>

				<?php if (isset($article['id'])): ?>
					<section
						class="blog-comments"
						data-blog-comments
						data-store-url="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/comments/"
						data-vote-url="/blog/<?= htmlspecialchars((string) $topic['slug']) ?>/<?= htmlspecialchars((string) $article['slug']) ?>/comments/vote/"
					>
						<h2 style="margin-bottom: 25px">Комментарии</h2>
						<div class="blog-comments__form-home" data-blog-comment-form-home>
							<form class="blog-comment-form" data-blog-comment-form>
								<input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
								<input type="hidden" name="parent_id" value="" data-blog-comment-parent>
								<div class="blog-comment-form__field">
									<div class="blog-comment-form__composer">
										<textarea name="comment" rows="1" placeholder="Напишите комментарий" aria-label="Текст комментария" data-blog-comment-text></textarea>
										<div class="blog-comment-form__tools">
											<button class="blog-comment-form__tool" type="button" aria-label="Добавить эмодзи" aria-expanded="false" data-blog-comment-emoji-toggle>
											<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false">
												<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.7"/>
												<circle cx="9" cy="10" r="1.2" fill="currentColor"/>
												<circle cx="15" cy="10" r="1.2" fill="currentColor"/>
												<path d="M8.4 14.2c1.2 1.4 2.4 2.1 3.6 2.1s2.4-.7 3.6-2.1" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
											</svg>
										</button>
											<button class="blog-comment-form__submit" type="submit" aria-label="Отправить комментарий" data-blog-comment-submit>
												<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M6.5 10.5 12 5l5.5 5.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"/></svg>
											</button>
										</div>
										<div class="blog-comment-form__emoji-panel" data-blog-comment-emoji-panel aria-label="Выберите эмодзи">
											<?php foreach (['😀', '😊', '👍', '❤️', '🎉', '🤝', '💡', '🔥', '👏', '😎', '🙌', '✨'] as $emoji): ?>
												<button class="blog-comment-form__emoji" type="button" data-blog-comment-emoji="<?= htmlspecialchars($emoji) ?>"><?= htmlspecialchars($emoji) ?></button>
											<?php endforeach; ?>
										</div>
									</div>
									<p class="blog-comment-form__counter" data-blog-comment-counter hidden></p>
								</div>
								<p class="blog-comment-form__message" data-blog-comment-message aria-live="polite"></p>
							</form>
						</div>
						<div class="blog-comments__list" data-blog-comments-list></div>
						<p class="blog-comments__empty" data-blog-comments-empty<?= $comments === [] ? '' : ' hidden' ?>>Напишите что-нибудь — ваш комментарий станет первым</p>
					</section>
				<?php endif; ?>
			</article>
		<?php endif; ?>
	</div>
</section>

<?php if (!$canEditArticle && isset($article['id'])): ?>
	<script>
	document.addEventListener('DOMContentLoaded', () => {
		const voteRoot = document.querySelector('[data-blog-vote]');
		if (!voteRoot) {
			return;
		}

		const stars = Array.from(voteRoot.querySelectorAll('.blog-article-vote__star'));
		const messageNode = voteRoot.querySelector('[data-blog-vote-message]');
		const csrfInput = voteRoot.querySelector('[data-blog-vote-csrf]');
		const summaryStars = document.querySelector('[data-blog-rating-stars]');
		const summaryValue = document.querySelector('[data-blog-rating-value]');
		let userRating = Number(voteRoot.getAttribute('data-user-rating') || 0);
		let isSubmitting = false;

		const renderStars = (container, value) => {
			if (!container) {
				return;
			}

			const filled = Math.max(0, Math.min(5, Math.round(Number(value) || 0)));
			container.innerHTML = '';

			for (let i = 1; i <= 5; i++) {
				const star = document.createElement('span');
				star.className = 'blog-rating__star' + (i <= filled ? ' is-active' : '');
				star.setAttribute('aria-hidden', 'true');
				star.textContent = '★';
				container.appendChild(star);
			}
		};

		const paintVoteStars = (value) => {
			stars.forEach((star) => {
				const rating = Number(star.getAttribute('data-rating') || 0);
				star.classList.toggle('is-active', rating > 0 && rating <= value);
			});
		};

		const applyVoteResult = (rating, average, message) => {
			userRating = rating;
			voteRoot.setAttribute('data-user-rating', String(rating));
			paintVoteStars(rating);
			renderStars(summaryStars, average);
			if (summaryValue) {
				summaryValue.textContent = Number(average || 0).toFixed(1);
			}
			if (messageNode) {
				messageNode.textContent = message || ('Ваша оценка: ' + rating + ' из 5');
			}
		};

		paintVoteStars(userRating);

		stars.forEach((star) => {
			star.addEventListener('mouseenter', () => {
				paintVoteStars(Number(star.getAttribute('data-rating') || 0));
			});

			star.addEventListener('focus', () => {
				paintVoteStars(Number(star.getAttribute('data-rating') || 0));
			});

			star.addEventListener('click', async () => {
				if (isSubmitting) {
					return;
				}

				const rating = Number(star.getAttribute('data-rating') || 0);
				if (rating < 1 || rating > 5) {
					return;
				}

				isSubmitting = true;
				const formData = new FormData();
				formData.append('_csrf', csrfInput ? csrfInput.value : '');
				formData.append('rating', String(rating));

				try {
					const response = await fetch(voteRoot.getAttribute('data-rate-url') || '', {
						method: 'POST',
						body: formData,
						credentials: 'same-origin'
					});
					const result = await response.json();

					if (!result || !result.success) {
						if (messageNode) {
							messageNode.textContent = (result && result.message) ? result.message : 'Не удалось сохранить оценку.';
						}
						return;
					}

					applyVoteResult(
						Number(result.user_rating || rating),
						result.average,
						result.message
					);
				} catch (error) {
					if (messageNode) {
						messageNode.textContent = 'Не удалось сохранить оценку.';
					}
				} finally {
					isSubmitting = false;
				}
			});
		});

		voteRoot.addEventListener('mouseleave', () => {
			paintVoteStars(userRating);
		});
	});
	</script>
<?php endif; ?>

<?php if (!$canEditArticle && isset($article['id'])): ?>
	<script>
	document.addEventListener('DOMContentLoaded', () => {
		const commentsRoot = document.querySelector('[data-blog-comments]');
		if (!commentsRoot) {
			return;
		}

		const commentsList = commentsRoot.querySelector('[data-blog-comments-list]');
		const emptyState = commentsRoot.querySelector('[data-blog-comments-empty]');
		const formHome = commentsRoot.querySelector('[data-blog-comment-form-home]');
		const form = commentsRoot.querySelector('[data-blog-comment-form]');
		const textarea = form ? form.querySelector('[data-blog-comment-text]') : null;
		const parentInput = form ? form.querySelector('[data-blog-comment-parent]') : null;
		const counter = form ? form.querySelector('[data-blog-comment-counter]') : null;
		const message = form ? form.querySelector('[data-blog-comment-message]') : null;
		const submitButton = form ? form.querySelector('[data-blog-comment-submit]') : null;
		const emojiToggle = form ? form.querySelector('[data-blog-comment-emoji-toggle]') : null;
		const emojiPanel = form ? form.querySelector('[data-blog-comment-emoji-panel]') : null;
		const csrfInput = form ? form.querySelector('input[name="_csrf"]') : null;
		const maxTextLength = 500;
		let comments = <?= json_encode($comments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
		let isSubmitting = false;

		if (!commentsList || !emptyState || !formHome || !form || !textarea || !parentInput || !counter || !message || !submitButton || !emojiToggle || !emojiPanel) {
			return;
		}

		const defaultAvatarUrl = '/Templates/Light/img/avatar-default.png';
		const createAvatar = (authorName, avatarUrl) => {
			const avatar = document.createElement('span');
			avatar.className = 'blog-comment__avatar';
			const image = document.createElement('img');
			image.src = avatarUrl || defaultAvatarUrl;
			image.alt = authorName || 'Аватар';
			image.loading = 'lazy';
			image.decoding = 'async';
			image.width = 40;
			image.height = 40;
			avatar.appendChild(image);
			return avatar;
		};
		const setMessage = (text, isError = false) => {
			message.textContent = text || '';
			message.classList.toggle('is-error', isError);
		};

		const updateCounter = () => {
			const length = Array.from(textarea.value).length;
			const remaining = maxTextLength - length;
			counter.classList.remove('is-warning', 'is-error');

			if (remaining < 0) {
				counter.hidden = false;
				counter.classList.add('is-error');
				counter.textContent = 'Превышен лимит в 500 символов';
			} else if (remaining <= 50) {
				counter.hidden = false;
				counter.classList.add('is-warning');
				counter.textContent = 'Осталось ' + remaining + ' символов';
			} else {
				counter.hidden = true;
				counter.textContent = '';
			}

			submitButton.disabled = remaining < 0 || isSubmitting;
		};

		const closeEmojiPanel = () => {
			emojiPanel.classList.remove('is-open');
			emojiToggle.setAttribute('aria-expanded', 'false');
		};

		const restoreFormHome = () => {
			formHome.appendChild(form);
			form.classList.remove('blog-comment-form_reply');
			parentInput.value = '';
			textarea.placeholder = 'Напишите комментарий';
			closeEmojiPanel();
		};

		const createVoteButton = (label, vote, count, active, dislike = false) => {
			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'blog-comment__vote' + (active ? ' is-active' : '') + (dislike ? ' is-dislike' : '');
			button.dataset.blogCommentVote = String(vote);
			button.setAttribute('aria-label', label);
			button.textContent = (dislike ? '👎 ' : '👍 ') + String(Number(count) || 0);
			return button;
		};

		const appendCommentText = (node, text, isReply = false) => {
			const value = String(text || '');
			const mentionMatch = isReply ? value.match(/^([^,\n]+),\s([\s\S]*)$/) : null;

			if (mentionMatch) {
				const mention = document.createElement('span');
				mention.className = 'blog-comment__text-mention';
				mention.textContent = mentionMatch[1] + ',';
				node.appendChild(mention);
				node.appendChild(document.createTextNode(' '));
				const lines = String(mentionMatch[2] || '').split(/\r\n|\r|\n/);
				lines.forEach((line, index) => {
					if (index > 0) {
						node.appendChild(document.createElement('br'));
					}
					node.appendChild(document.createTextNode(line));
				});
				return;
			}

			const lines = value.split(/\r\n|\r|\n/);
			lines.forEach((line, index) => {
				if (index > 0) {
					node.appendChild(document.createElement('br'));
				}
				node.appendChild(document.createTextNode(line));
			});
		};

		const createComment = (comment, depth = 0) => {
			const safeDepth = depth > 0 ? 1 : 0;
			const item = document.createElement('article');
			item.className = 'blog-comment' + (safeDepth > 0 ? ' blog-comment_reply' : '');
			item.dataset.blogCommentId = String(Number(comment.id) || 0);
			item.dataset.blogCommentDepth = String(safeDepth);
			item.style.setProperty('--blog-comment-depth', String(safeDepth));

			const header = document.createElement('header');
			header.className = 'blog-comment__header';
			const author = String(comment.author || 'Аноним');
			header.appendChild(createAvatar(author, comment.avatar || defaultAvatarUrl));
			const authorNode = document.createElement('span');
			authorNode.className = 'blog-comment__author';
			authorNode.textContent = author;
			header.appendChild(authorNode);
			if (comment.date) {
				const date = document.createElement('time');
				date.className = 'blog-comment__date';
				date.textContent = String(comment.date);
				header.appendChild(date);
			}
			item.appendChild(header);

			const text = document.createElement('p');
			text.className = 'blog-comment__text';
			appendCommentText(text, comment.text, safeDepth > 0);
			item.appendChild(text);

			const actions = document.createElement('div');
			actions.className = 'blog-comment__actions';
			const reply = document.createElement('button');
			reply.type = 'button';
			reply.className = 'blog-comment__reply';
			reply.dataset.blogCommentReply = String(Number(comment.id) || 0);
			reply.dataset.blogCommentAuthor = author;
			reply.textContent = 'Ответить';
			actions.appendChild(reply);
			actions.appendChild(createVoteButton('Нравится', 1, comment.likes, Number(comment.user_vote) === 1));
			actions.appendChild(createVoteButton('Не нравится', -1, comment.dislikes, Number(comment.user_vote) === -1, true));
			item.appendChild(actions);

			const replies = Array.isArray(comment.replies) ? comment.replies : [];
			if (replies.length > 0 && safeDepth === 0) {
				const repliesNode = document.createElement('div');
				repliesNode.className = 'blog-comment__replies';
				replies.forEach((child) => {
					// Второй уровень — максимум: вложенные ответы рисуем рядом.
					repliesNode.appendChild(createComment(child, 1));
					const nested = Array.isArray(child.replies) ? child.replies : [];
					nested.forEach((grandChild) => {
						repliesNode.appendChild(createComment(grandChild, 1));
					});
				});
				item.appendChild(repliesNode);
			}

			return item;
		};

		const renderComments = () => {
			if (form.parentElement !== formHome) {
				restoreFormHome();
			}
			commentsList.replaceChildren();
			(Array.isArray(comments) ? comments : []).forEach((comment) => {
				commentsList.appendChild(createComment(comment));
			});
			emptyState.hidden = Array.isArray(comments) && comments.length > 0;
		};

		emojiToggle.addEventListener('click', () => {
			const isOpen = emojiPanel.classList.toggle('is-open');
			emojiToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});

		emojiPanel.addEventListener('click', (event) => {
			const emojiButton = event.target.closest('[data-blog-comment-emoji]');
			if (!emojiButton) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();

			const emoji = (emojiButton.dataset.blogCommentEmoji || emojiButton.textContent || '').trim();
			if (emoji === '') {
				return;
			}

			const start = textarea.selectionStart ?? textarea.value.length;
			const end = textarea.selectionEnd ?? textarea.value.length;
			const value = textarea.value || '';
			textarea.value = value.slice(0, start) + emoji + value.slice(end);
			const cursor = start + emoji.length;
			textarea.focus();
			textarea.setSelectionRange(cursor, cursor);
			textarea.dispatchEvent(new Event('input'));
		});

		document.addEventListener('click', (event) => {
			if (event.target.closest('[data-blog-comment-emoji-panel]') || event.target.closest('[data-blog-comment-emoji-toggle]')) {
				return;
			}
			closeEmojiPanel();
		});

		textarea.addEventListener('input', () => {
			textarea.style.height = 'auto';
			textarea.style.height = Math.min(Math.max(textarea.scrollHeight, 24), 160) + 'px';
			updateCounter();
		});
		updateCounter();
		renderComments();

		commentsList.addEventListener('click', async (event) => {
			const replyButton = event.target.closest('[data-blog-comment-reply]');
			if (replyButton) {
				const commentNode = replyButton.closest('[data-blog-comment-id]');
				if (!commentNode) {
					return;
				}

				const authorName = (replyButton.dataset.blogCommentAuthor || (commentNode.querySelector('.blog-comment__author') || {}).textContent || 'Аноним').trim();
				const rootComment = commentNode.classList.contains('blog-comment_reply')
					? commentNode.parentElement.closest('[data-blog-comment-id]')
					: commentNode;
				const mountNode = rootComment || commentNode;
				const repliesNode = mountNode.querySelector(':scope > .blog-comment__replies');

				form.classList.add('blog-comment-form_reply');
				parentInput.value = replyButton.dataset.blogCommentReply || '';
				textarea.placeholder = 'Ответ для ' + authorName;
				if (repliesNode) {
					mountNode.insertBefore(form, repliesNode);
				} else {
					mountNode.appendChild(form);
				}
				textarea.focus();
				return;
			}

			const voteButton = event.target.closest('[data-blog-comment-vote]');
			const commentNode = voteButton ? voteButton.closest('[data-blog-comment-id]') : null;
			if (!voteButton || !commentNode) {
				return;
			}

			const formData = new FormData();
			formData.append('_csrf', csrfInput ? csrfInput.value : '');
			formData.append('comment_id', commentNode.dataset.blogCommentId || '');
			formData.append('vote', voteButton.dataset.blogCommentVote || '');

			try {
				const response = await fetch(commentsRoot.dataset.voteUrl || '', {
					method: 'POST',
					body: formData,
					credentials: 'same-origin'
				});
				const result = await response.json();
				if (!result || !result.success) {
					setMessage((result && result.message) || 'Не удалось сохранить голос.', true);
					return;
				}
				comments = Array.isArray(result.comments) ? result.comments : comments;
				renderComments();
			} catch (error) {
				setMessage('Не удалось сохранить голос.', true);
			}
		});

		form.addEventListener('submit', async (event) => {
			event.preventDefault();
			if (isSubmitting || Array.from(textarea.value).length > maxTextLength) {
				updateCounter();
				return;
			}

			isSubmitting = true;
			updateCounter();
			setMessage('');
			const formData = new FormData(form);

			try {
				const response = await fetch(commentsRoot.dataset.storeUrl || '', {
					method: 'POST',
					body: formData,
					credentials: 'same-origin'
				});
				const result = await response.json();
				if (!result || !result.success) {
					setMessage((result && result.message) || 'Не удалось добавить комментарий.', true);
					return;
				}
				comments = Array.isArray(result.comments) ? result.comments : comments;
				textarea.value = '';
				restoreFormHome();
				renderComments();
				setMessage(result.message || 'Комментарий добавлен.');
			} catch (error) {
				setMessage('Не удалось добавить комментарий.', true);
			} finally {
				isSubmitting = false;
				updateCounter();
			}
		});
	});
	</script>
<?php endif; ?>
