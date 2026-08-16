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

$renderStarsMarkup = static function (float $value, int $max = 5): string {
	$filled = (int) max(0, min($max, round($value)));
	$markup = '';

	for ($i = 1; $i <= $max; $i++) {
		$markup .= '<span class="blog-rating__star' . ($i <= $filled ? ' is-active' : '') . '" aria-hidden="true">★</span>';
	}

	return $markup;
};

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

$canEditArticle = $isAdmin && $editMode && isset($article['id']);
$comments = is_array($data['comments'] ?? null) ? $data['comments'] : [];
$seoForm = is_array($data['seo_form'] ?? null) ? $data['seo_form'] : [];
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
		<div class="blog-detail-hero__title-block">
			<h2><?= htmlspecialchars((string) $article['title']) ?></h2>
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

<?php if ($canEditArticle): ?>
	<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.8/dist/editorjs.umd.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.8/dist/header.umd.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@1.10.0/dist/list.umd.min.js"></script>
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

		if (!window.EditorJS || !window.List || !editorRoot || !editorInput || !blocksInput || !form) {
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

		class SuperscriptTool extends InlineTagTool {}
		SuperscriptTool.tag = 'sup';
		SuperscriptTool.title = 'Верхняя сноска';
		SuperscriptTool.icon = 'x²';

		class SubscriptTool extends InlineTagTool {}
		SubscriptTool.tag = 'sub';
		SubscriptTool.title = 'Нижняя сноска';
		SubscriptTool.icon = 'x₂';

		const createWrapMark = () => {
			const mark = document.createElement('span');
			mark.className = 'blog-wrap';
			mark.contentEditable = 'false';
			mark.appendChild(document.createElement('wbr'));
			return mark;
		};

		const insertMarksAfterSign = (root, sign) => {
			if (!root || !sign) {
				return;
			}

			const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
				acceptNode: (node) => {
					const parent = node.parentElement;
					if (parent && parent.closest('.blog-wrap, .blog-nowrap')) {
						return NodeFilter.FILTER_REJECT;
					}

					return NodeFilter.FILTER_ACCEPT;
				}
			});
			const nodes = [];
			while (walker.nextNode()) {
				nodes.push(walker.currentNode);
			}

			nodes.forEach((node) => {
				const text = node.nodeValue || '';
				if (!text.includes(sign)) {
					return;
				}

				const fragment = document.createDocumentFragment();
				let cursor = 0;
				let changed = false;

				while (cursor < text.length) {
					const index = text.indexOf(sign, cursor);
					if (index === -1) {
						fragment.appendChild(document.createTextNode(text.slice(cursor)));
						break;
					}

					const afterSign = index + sign.length;
					fragment.appendChild(document.createTextNode(text.slice(cursor, afterSign)));
					cursor = afterSign;

					const isEnd = afterSign === text.length;
					const next = node.nextSibling;
					const alreadyWrapped = isEnd && next instanceof Element && next.classList.contains('blog-wrap');
					if (!alreadyWrapped) {
						fragment.appendChild(createWrapMark());
						changed = true;
					}
				}

				if (changed && node.parentNode) {
					node.parentNode.replaceChild(fragment, node);
				}
			});
		};

		class WrapMarkTool {
			static get isInline() {
				return true;
			}

			static get sanitize() {
				return {
					wbr: true,
					span: {
						class: true
					}
				};
			}

			constructor({ api }) {
				this.api = api;
				this.button = null;
			}

			render() {
				this.button = document.createElement('button');
				this.button.type = 'button';
				this.button.classList.add(this.api.styles.inlineToolButton);
				this.button.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h10M4 12h7M4 17h10"/><path d="M16 14l3 3 3-3M19 7v10"/></svg>';
				this.button.title = 'Перенос за знак';
				return this.button;
			}

			surround(range) {
				if (!range) {
					return;
				}

				if (range.collapsed) {
					const mark = createWrapMark();
					range.insertNode(mark);
					range.setStartAfter(mark);
					range.collapse(true);
					const selection = window.getSelection();
					selection.removeAllRanges();
					selection.addRange(range);
					return;
				}

				const selectedText = range.toString();
				if (selectedText.length === 1) {
					const fragment = document.createDocumentFragment();
					fragment.appendChild(range.extractContents());
					fragment.appendChild(createWrapMark());
					range.insertNode(fragment);
					return;
				}

				const sign = window.prompt('Введите знак, после которого разрешить перенос', '/');
				if (!sign) {
					return;
				}

				const container = document.createElement('div');
				container.appendChild(range.extractContents());
				insertMarksAfterSign(container, sign);
				range.insertNode(container);
				container.replaceWith(...container.childNodes);
			}

			checkState(selection) {
				if (!this.button) {
					return;
				}

				const parent = selection.anchorNode && selection.anchorNode.parentElement
					? selection.anchorNode.parentElement
					: null;
				const active = Boolean(parent && parent.closest('.blog-wrap'));
				this.button.classList.toggle(this.api.styles.inlineToolButtonActive, active);
			}
		}

		class PinTextTool {
			static get isInline() {
				return true;
			}

			static get sanitize() {
				return {
					span: {
						class: true
					}
				};
			}

			constructor({ api }) {
				this.api = api;
				this.button = null;
			}

			render() {
				this.button = document.createElement('button');
				this.button.type = 'button';
				this.button.classList.add(this.api.styles.inlineToolButton);
				this.button.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 17v5"/><path d="M8 3h8l-1 7h3l-6 7-6-7h3z"/></svg>';
				this.button.title = 'Закрепить без переноса';
				return this.button;
			}

			surround(range) {
				if (!range || range.collapsed) {
					return;
				}

				const parent = range.commonAncestorContainer instanceof Element
					? range.commonAncestorContainer
					: range.commonAncestorContainer.parentElement;
				const existing = parent && parent.closest ? parent.closest('span.blog-nowrap') : null;
				if (existing) {
					existing.replaceWith(...existing.childNodes);
					return;
				}

				const wrapper = document.createElement('span');
				wrapper.className = 'blog-nowrap';
				wrapper.appendChild(range.extractContents());
				range.insertNode(wrapper);
				this.api.selection.expandToTag(wrapper);
			}

			checkState(selection) {
				if (!this.button) {
					return;
				}

				const parent = selection.anchorNode && selection.anchorNode.parentElement
					? selection.anchorNode.parentElement
					: null;
				const active = Boolean(parent && parent.closest('span.blog-nowrap'));
				this.button.classList.toggle(this.api.styles.inlineToolButtonActive, active);
			}
		}

		class CopyBlockTune {
			static get isTune() {
				return true;
			}

			constructor({ api, block }) {
				this.api = api;
				this.block = block;
			}

			render() {
				return {
					icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M4 16V6a2 2 0 0 1 2-2h10"/></svg>',
					label: 'Копировать',
					title: 'Копировать',
					closeOnActivate: true,
					onActivate: () => {
						this.duplicate();
					}
				};
			}

			async duplicate() {
				const saved = await this.block.save();
				const data = JSON.parse(JSON.stringify((saved && saved.data) || {}));
				this.api.blocks.insert(this.block.name, data);
			}
		}

		class BlogList extends List {
			static get sanitize() {
				const base = List.sanitize && typeof List.sanitize === 'object' ? List.sanitize : {};
				return {
					style: base.style || {},
					items: Object.assign({ br: true, wbr: true, span: true }, base.items || {})
				};
			}

			backspace(event) {
				let current = null;
				try {
					current = this.currentItem;
				} catch (error) {
					super.backspace(event);
					return;
				}

				if (!current || !this.isEmptyItem(current)) {
					super.backspace(event);
					return;
				}

				const items = this._elements.wrapper.querySelectorAll(':scope > .' + this.CSS.item);
				if (items.length < 2) {
					super.backspace(event);
					return;
				}

				event.preventDefault();
				event.stopPropagation();

				const previous = current.previousElementSibling;
				const next = current.nextElementSibling;
				current.remove();

				const target = previous || next;
				if (target) {
					this.placeCaret(target, !previous);
				}
			}

			isEmptyItem(item) {
				const clone = item.cloneNode(true);
				clone.querySelectorAll('.blog-wrap, wbr').forEach((node) => node.remove());
				const text = String(clone.textContent || '').replace(/\u00a0/g, ' ').replace(/\u200B/g, '').trim();
				const html = String(clone.innerHTML || '')
					.replace(/<br\b[^>]*>/gi, '')
					.replace(/&nbsp;/gi, '')
					.trim();
				return text === '' && html === '';
			}

			placeCaret(item, atStart) {
				const range = document.createRange();
				range.selectNodeContents(item);
				range.collapse(Boolean(atStart));
				const selection = window.getSelection();
				selection.removeAllRanges();
				selection.addRange(range);
			}
		}

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

		class TableBlockTool {
			static get toolbox() {
				return {
					title: 'Таблица',
					icon: '<svg width="17" height="15" viewBox="0 0 17 15"><rect x="1" y="1" width="15" height="13" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M1 5h15M1 10h15M6 1v13M11 1v13" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>'
				};
			}

			static get pasteConfig() {
				return {
					tags: ['TABLE']
				};
			}

			static get icons() {
				return {
					plus: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>',
					removeRow: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="5" width="13" height="14" rx="1.5"/><path d="M3 12h13M9.5 5v14"/><path d="M16.5 9h6"/></svg>',
					removeColumn: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="3" width="14" height="13" rx="1.5"/><path d="M3 9.5h14M10 3v13"/><path d="M10 20h6"/></svg>',
					alignH: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M7 12h10M4 17h16"/></svg>',
					alignV: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 4v16M8 9h8M8 15h8"/></svg>'
				};
			}

			constructor({ data, api, block }) {
				this.api = api;
				this.block = block || null;
				this.needsSizeDialog = !this.hasSavedRows(data);
				this.sizeDialogOpened = false;
				this.focusedCell = { row: 0, col: 0 };
				this.data = this.normalizeData(data);
				this.wrapper = null;
			}

			hasSavedRows(data) {
				return Boolean(data && Array.isArray(data.rows) && data.rows.length > 0);
			}

			normalizeData(data) {
				const source = data && typeof data === 'object' ? data : {};
				const rows = Array.isArray(source.rows)
					? source.rows
						.map((row) => Array.isArray(row) ? row.map((cell) => String(cell ?? '')) : null)
						.filter((row) => row !== null && row.length > 0)
					: [];

				const normalizedRows = rows.length > 0 ? rows : this.createEmptyRows(2, 2);
				const columnCount = Math.max(...normalizedRows.map((row) => row.length), 1);

				return {
					withHead: source.withHead !== false,
					caption: String(source.caption || ''),
					alignH: source.alignH === 'center' ? 'center' : 'left',
					alignV: source.alignV === 'middle' ? 'middle' : 'top',
					cellAlign: this.normalizeCellAlign(source.cellAlign),
					rows: normalizedRows.map((row) => {
						const normalized = row.slice(0, columnCount);
						while (normalized.length < columnCount) {
							normalized.push('');
						}
						return normalized;
					})
				};
			}

			normalizeCellAlign(source) {
				if (!source || typeof source !== 'object' || Array.isArray(source)) {
					return {};
				}

				const result = {};
				Object.keys(source).forEach((key) => {
					if (!/^\d+-\d+$/.test(key) || !source[key] || typeof source[key] !== 'object') {
						return;
					}

					const align = {};
					if (source[key].h === 'center') {
						align.h = 'center';
					}
					if (source[key].v === 'middle') {
						align.v = 'middle';
					}
					if (align.h || align.v) {
						result[key] = align;
					}
				});

				return result;
			}

			createEmptyRows(rowCount, columnCount) {
				return Array.from({ length: rowCount }, () => Array.from({ length: columnCount }, () => ''));
			}

			render() {
				this.wrapper = document.createElement('div');
				this.wrapper.className = 'blog-editor-table-block';

				if (this.needsSizeDialog) {
					this.wrapper.innerHTML = '<p class="blog-editor-table-block__placeholder">Укажите размер таблицы</p>';
					window.setTimeout(() => this.openSizeDialog(), 0);
				} else {
					this.draw();
				}

				return this.wrapper;
			}

			openSizeDialog() {
				if (this.sizeDialogOpened || !this.needsSizeDialog) {
					return;
				}

				this.sizeDialogOpened = true;

				const overlay = document.createElement('div');
				overlay.className = 'blog-settings-modal is-open blog-editor-table-dialog';
				overlay.innerHTML = `
					<div class="blog-settings-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="blog-editor-table-dialog-title">
						<div class="blog-settings-modal__header">
							<h2 class="blog-settings-modal__title" id="blog-editor-table-dialog-title">Новая таблица</h2>
							<button class="blog-settings-modal__close" type="button" data-table-dialog-close aria-label="Закрыть">×</button>
						</div>
						<form class="blog-settings-modal__form">
							<div class="blog-editor-table-dialog__sizes">
								<div>
									<label class="blog-settings-modal__label" for="blog-editor-table-rows">Строки</label>
									<input class="blog-settings-modal__input" id="blog-editor-table-rows" name="rows" type="number" min="1" max="30" value="2" required>
								</div>
								<div>
									<label class="blog-settings-modal__label" for="blog-editor-table-cols">Столбцы</label>
									<input class="blog-settings-modal__input" id="blog-editor-table-cols" name="cols" type="number" min="1" max="20" value="2" required>
								</div>
							</div>
							<div class="blog-settings-modal__actions">
								<button class="blog-settings-modal__button blog-settings-modal__button_secondary" type="button" data-table-dialog-close>Отмена</button>
								<button class="blog-settings-modal__button" type="submit">Создать</button>
							</div>
						</form>
					</div>
				`;

				const form = overlay.querySelector('form');
				const rowsInput = overlay.querySelector('#blog-editor-table-rows');
				const closeDialog = (confirmed) => {
					overlay.remove();
					document.body.classList.remove('blog-settings-modal-open');
					document.removeEventListener('keydown', onKeyDown);

					if (confirmed) {
						return;
					}

					this.deleteCurrentBlock();
				};

				const onKeyDown = (event) => {
					if (event.key === 'Escape') {
						event.preventDefault();
						closeDialog(false);
					}
				};

				overlay.addEventListener('click', (event) => {
					if (event.target === overlay || event.target.closest('[data-table-dialog-close]')) {
						closeDialog(false);
					}
				});

				form.addEventListener('submit', (event) => {
					event.preventDefault();
					event.stopPropagation();

					const rowCount = this.clampSize(rowsInput.value, 1, 30, 2);
					const columnCount = this.clampSize(overlay.querySelector('#blog-editor-table-cols').value, 1, 20, 2);
					this.data.rows = this.createEmptyRows(rowCount, columnCount);
					this.data.cellAlign = {};
					this.needsSizeDialog = false;
					this.draw();
					closeDialog(true);
				});

				document.addEventListener('keydown', onKeyDown);
				document.body.classList.add('blog-settings-modal-open');
				document.body.appendChild(overlay);
				rowsInput.focus();
				rowsInput.select();
			}

			clampSize(value, min, max, fallback) {
				const parsed = Number.parseInt(value, 10);
				if (!Number.isFinite(parsed)) {
					return fallback;
				}

				return Math.min(max, Math.max(min, parsed));
			}

			deleteCurrentBlock() {
				try {
					if (this.block && this.block.id) {
						this.api.blocks.delete(this.block.id);
						return;
					}

					this.api.blocks.delete();
				} catch (error) {
					this.needsSizeDialog = false;
					this.draw();
				}
			}

			draw() {
				if (!this.wrapper) {
					return;
				}

				this.wrapper.innerHTML = '';

				const captionInput = document.createElement('input');
				captionInput.type = 'text';
				captionInput.className = 'blog-editor-table-block__caption';
				captionInput.placeholder = 'Заголовок таблицы (legend)';
				captionInput.value = this.data.caption || '';
				captionInput.addEventListener('input', () => {
					this.data.caption = captionInput.value;
				});
				this.wrapper.appendChild(captionInput);

				const table = document.createElement('table');
				table.className = 'blog-editor-table-block__table blog-table';
				if (this.data.alignH === 'center') {
					table.classList.add('blog-table_h-center');
				}
				if (this.data.alignV === 'middle') {
					table.classList.add('blog-table_v-middle');
				}

				this.data.rows.forEach((row, rowIndex) => {
					const tr = document.createElement('tr');
					tr.dataset.tableRow = String(rowIndex);
					this.appendInsertCell(tr, 'Добавить столбец слева', () => this.insertColumn(0));

					row.forEach((cellHtml, cellIndex) => {
						const cell = document.createElement(this.data.withHead && rowIndex === 0 ? 'th' : 'td');
						cell.contentEditable = 'true';
						cell.innerHTML = cellHtml;
						cell.dataset.row = String(rowIndex);
						cell.dataset.col = String(cellIndex);
						this.applyCellAlignClass(cell, rowIndex, cellIndex);
						cell.addEventListener('focus', () => {
							this.focusedCell = { row: rowIndex, col: cellIndex };
						});
						tr.appendChild(cell);
					});

					this.appendInsertCell(tr, 'Добавить строку ниже', () => this.insertRow(rowIndex + 1));
					table.appendChild(tr);
				});

				const insertColsRow = document.createElement('tr');
				insertColsRow.className = 'blog-editor-table-block__insert-row';
				this.appendInsertCell(insertColsRow, 'Добавить строку сверху', () => this.insertRow(0));
				const columnCount = this.data.rows[0] ? this.data.rows[0].length : 0;
				for (let columnIndex = 0; columnIndex < columnCount; columnIndex++) {
					this.appendInsertCell(insertColsRow, 'Добавить столбец справа', () => this.insertColumn(columnIndex + 1));
				}
				const corner = document.createElement('td');
				corner.className = 'blog-editor-table-block__insert';
				insertColsRow.appendChild(corner);
				table.appendChild(insertColsRow);

				this.wrapper.appendChild(table);
			}

			applyCellAlignClass(cell, rowIndex, cellIndex) {
				const align = this.data.cellAlign[rowIndex + '-' + cellIndex] || {};
				if (align.h === 'center') {
					cell.classList.add('blog-table-cell_h-center');
				}
				if (align.v === 'middle') {
					cell.classList.add('blog-table-cell_v-middle');
				}
			}

			appendInsertCell(tr, title, onClick) {
				const cell = document.createElement('td');
				cell.className = 'blog-editor-table-block__insert';
				cell.contentEditable = 'false';

				const button = document.createElement('button');
				button.type = 'button';
				button.className = 'blog-editor-table-block__insert-button';
				button.title = title;
				button.setAttribute('aria-label', title);
				button.innerHTML = TableBlockTool.icons.plus;
				button.addEventListener('mousedown', (event) => event.preventDefault());
				button.addEventListener('click', (event) => {
					event.preventDefault();
					event.stopPropagation();
					this.readFromDom();
					onClick();
					this.draw();
				});

				cell.appendChild(button);
				tr.appendChild(cell);
			}

			insertRow(index) {
				const columns = this.data.rows[0] ? this.data.rows[0].length : 1;
				this.data.rows.splice(index, 0, Array.from({ length: columns }, () => ''));
				this.shiftCellAlign((row, col) => [row >= index ? row + 1 : row, col]);
			}

			insertColumn(index) {
				this.data.rows.forEach((row) => {
					row.splice(index, 0, '');
				});
				this.shiftCellAlign((row, col) => [row, col >= index ? col + 1 : col]);
			}

			removeRowAt(index) {
				if (this.data.rows.length <= 1) {
					return;
				}

				this.data.rows.splice(index, 1);
				this.shiftCellAlign((row, col) => (row === index ? null : [row > index ? row - 1 : row, col]));
				this.focusedCell.row = Math.min(this.focusedCell.row, this.data.rows.length - 1);
			}

			removeColumnAt(index) {
				if ((this.data.rows[0] || []).length <= 1) {
					return;
				}

				this.data.rows.forEach((row) => row.splice(index, 1));
				this.shiftCellAlign((row, col) => (col === index ? null : [row, col > index ? col - 1 : col]));
				this.focusedCell.col = Math.min(this.focusedCell.col, (this.data.rows[0] || []).length - 1);
			}

			shiftCellAlign(mapper) {
				const next = {};
				Object.keys(this.data.cellAlign || {}).forEach((key) => {
					const parts = key.split('-').map(Number);
					const mapped = mapper(parts[0], parts[1]);
					if (!mapped) {
						return;
					}

					next[mapped[0] + '-' + mapped[1]] = this.data.cellAlign[key];
				});
				this.data.cellAlign = next;
			}

			toggleTableAlign(axis) {
				if (axis === 'h') {
					this.data.alignH = this.data.alignH === 'center' ? 'left' : 'center';
					return;
				}

				this.data.alignV = this.data.alignV === 'middle' ? 'top' : 'middle';
			}

			toggleCellAlign(axis) {
				const key = this.focusedCell.row + '-' + this.focusedCell.col;
				const current = Object.assign({}, this.data.cellAlign[key] || {});

				if (axis === 'h') {
					current.h = current.h === 'center' ? '' : 'center';
				} else {
					current.v = current.v === 'middle' ? '' : 'middle';
				}

				if (!current.h && !current.v) {
					delete this.data.cellAlign[key];
					return;
				}

				this.data.cellAlign[key] = current;
			}

			isCellAlignActive(axis) {
				const align = this.data.cellAlign[this.focusedCell.row + '-' + this.focusedCell.col] || {};
				return axis === 'h' ? align.h === 'center' : align.v === 'middle';
			}

			renderSettings() {
				const wrapper = document.createElement('div');
				const icons = TableBlockTool.icons;

				const appendGroup = (label) => {
					if (label) {
						const title = document.createElement('div');
						title.className = 'blog-editor-table-settings__label';
						title.textContent = label;
						wrapper.appendChild(title);
					}

					const group = document.createElement('div');
					group.className = 'blog-editor-table-settings';
					wrapper.appendChild(group);
					return group;
				};

				const createButton = (group, title, icon, isActiveFn, onClick) => {
					const button = document.createElement('div');
					const refresh = () => {
						button.classList.toggle(this.api.styles.settingsButtonActive, Boolean(isActiveFn && isActiveFn()));
					};
					button.className = this.api.styles.settingsButton;
					button.innerHTML = icon;
					button.title = title;
					button.setAttribute('aria-label', title);
					refresh();
					button.addEventListener('click', () => {
						this.readFromDom();
						onClick();
						this.draw();
						refresh();
					});
					group.appendChild(button);
				};

				const tableGroup = appendGroup('Таблица');
				createButton(tableGroup, 'Центрировать таблицу по горизонтали', icons.alignH, () => this.data.alignH === 'center', () => {
					this.toggleTableAlign('h');
				});
				createButton(tableGroup, 'Центрировать таблицу по вертикали', icons.alignV, () => this.data.alignV === 'middle', () => {
					this.toggleTableAlign('v');
				});

				const cellGroup = appendGroup('Ячейка');
				createButton(cellGroup, 'Центрировать ячейку по горизонтали', icons.alignH, () => this.isCellAlignActive('h'), () => {
					this.toggleCellAlign('h');
				});
				createButton(cellGroup, 'Центрировать ячейку по вертикали', icons.alignV, () => this.isCellAlignActive('v'), () => {
					this.toggleCellAlign('v');
				});

				const sizeGroup = appendGroup('');
				createButton(sizeGroup, 'Убрать строку', icons.removeRow, null, () => {
					this.removeRowAt(this.focusedCell.row);
				});
				createButton(sizeGroup, 'Убрать столбец', icons.removeColumn, null, () => {
					this.removeColumnAt(this.focusedCell.col);
				});

				const headButton = document.createElement('div');
				headButton.className = this.api.styles.settingsButton + (this.data.withHead ? ' ' + this.api.styles.settingsButtonActive : '');
				headButton.textContent = 'Заголовок';
				headButton.addEventListener('click', () => {
					this.readFromDom();
					this.data.withHead = !this.data.withHead;
					headButton.classList.toggle(this.api.styles.settingsButtonActive, this.data.withHead);
					this.draw();
				});
				wrapper.appendChild(headButton);

				return wrapper;
			}

			readFromDom() {
				if (!this.wrapper) {
					return;
				}

				const captionInput = this.wrapper.querySelector('.blog-editor-table-block__caption');
				if (captionInput) {
					this.data.caption = captionInput.value;
				}

				const rows = [];
				this.wrapper.querySelectorAll('tr[data-table-row]').forEach((tr) => {
					const cells = [];
					tr.querySelectorAll('th, td').forEach((cell) => {
						if (cell.classList.contains('blog-editor-table-block__insert')) {
							return;
						}

						cells.push(cell.innerHTML);
					});
					if (cells.length > 0) {
						rows.push(cells);
					}
				});

				if (rows.length > 0) {
					this.data.rows = rows;
				}
			}

			save() {
				this.readFromDom();
				return {
					withHead: this.data.withHead !== false,
					caption: this.data.caption || '',
					alignH: this.data.alignH === 'center' ? 'center' : 'left',
					alignV: this.data.alignV === 'middle' ? 'middle' : 'top',
					cellAlign: this.data.cellAlign || {},
					rows: this.data.rows
				};
			}

			onPaste(event) {
				const table = event.detail && event.detail.data ? event.detail.data : null;
				if (!(table instanceof HTMLElement) || table.tagName !== 'TABLE') {
					return;
				}

				this.needsSizeDialog = false;
				this.sizeDialogOpened = true;
				this.data = this.normalizeData(parseTableElement(table));
				if (this.wrapper) {
					this.draw();
				}
			}
		}

		class AlertBlockTool {
			static get toolbox() {
				return {
					title: 'Alert',
					icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8h.01M11 12h1v4h1"/></svg>'
				};
			}

			static get enableLineBreaks() {
				return true;
			}

			static get styles() {
				return [
					{ id: 'info', title: 'Инфо', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8h.01M11 12h1v4h1"/></svg>' },
					{ id: 'warning', title: 'Внимание', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l10 18H2L12 3z"/><path d="M12 10v4M12 17h.01"/></svg>' },
					{ id: 'danger', title: 'Опасно', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>' },
					{ id: 'success', title: 'Успех', icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/></svg>' }
				];
			}

			constructor({ data, api }) {
				this.api = api;
				this.data = {
					style: this.normalizeStyle(data && data.style),
					text: data && data.text ? String(data.text) : ''
				};
				this.wrapper = null;
				this.textEl = null;
			}

			normalizeStyle(style) {
				return ['info', 'warning', 'danger', 'success'].includes(style) ? style : 'info';
			}

			render() {
				this.wrapper = document.createElement('div');
				this.applyStyle();

				this.textEl = document.createElement('div');
				this.textEl.className = 'blog-editor-alert-block__text';
				this.textEl.contentEditable = 'true';
				this.textEl.dataset.placeholder = 'Текст уведомления';
				this.textEl.innerHTML = this.data.text || '';
				this.wrapper.appendChild(this.textEl);

				return this.wrapper;
			}

			applyStyle() {
				if (!this.wrapper) {
					return;
				}

				this.wrapper.className = 'blog-editor-alert-block blog-editor-alert-block_' + this.data.style;
			}

			renderSettings() {
				const wrapper = document.createElement('div');
				wrapper.className = 'blog-editor-alert-settings';

				AlertBlockTool.styles.forEach((item) => {
					const button = document.createElement('div');
					button.className = this.api.styles.settingsButton;
					button.innerHTML = item.icon;
					button.title = item.title;
					button.setAttribute('aria-label', item.title);
					button.classList.toggle(this.api.styles.settingsButtonActive, this.data.style === item.id);
					button.addEventListener('click', () => {
						this.data.style = item.id;
						this.applyStyle();
						wrapper.querySelectorAll('.' + this.api.styles.settingsButton).forEach((node) => {
							node.classList.toggle(this.api.styles.settingsButtonActive, node === button);
						});
					});
					wrapper.appendChild(button);
				});

				return wrapper;
			}

			save() {
				return {
					style: this.data.style,
					text: this.textEl ? this.textEl.innerHTML : (this.data.text || '')
				};
			}
		}

		class SpoilerBlockTool {
			static get toolbox() {
				return {
					title: 'Раскрывающийся блок',
					icon: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h10M4 18h16"/><path d="M16 9l4 3-4 3"/></svg>'
				};
			}

			static get enableLineBreaks() {
				return true;
			}

			constructor({ data, api }) {
				this.api = api;
				this.data = {
					title: data && data.title ? String(data.title) : '',
					text: data && data.text ? String(data.text) : '',
					opened: Boolean(data && data.opened)
				};
				this.wrapper = null;
				this.titleEl = null;
				this.textEl = null;
			}

			render() {
				this.wrapper = document.createElement('div');
				this.wrapper.className = 'blog-editor-spoiler-block';

				this.titleEl = document.createElement('div');
				this.titleEl.className = 'blog-editor-spoiler-block__title';
				this.titleEl.contentEditable = 'true';
				this.titleEl.dataset.placeholder = 'Заголовок';
				this.titleEl.innerHTML = this.data.title || '';

				this.textEl = document.createElement('div');
				this.textEl.className = 'blog-editor-spoiler-block__text';
				this.textEl.contentEditable = 'true';
				this.textEl.dataset.placeholder = 'Скрытый текст';
				this.textEl.innerHTML = this.data.text || '';

				this.wrapper.appendChild(this.titleEl);
				this.wrapper.appendChild(this.textEl);
				return this.wrapper;
			}

			renderSettings() {
				const button = document.createElement('div');
				button.className = this.api.styles.settingsButton + (this.data.opened ? ' ' + this.api.styles.settingsButtonActive : '');
				button.textContent = 'Открыт';
				button.addEventListener('click', () => {
					this.data.opened = !this.data.opened;
					button.classList.toggle(this.api.styles.settingsButtonActive, this.data.opened);
				});
				return button;
			}

			save() {
				return {
					title: this.titleEl ? this.titleEl.innerHTML : (this.data.title || ''),
					text: this.textEl ? this.textEl.innerHTML : (this.data.text || ''),
					opened: Boolean(this.data.opened)
				};
			}
		}

		const escapeHtml = (value) => String(value || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');

		const flattenBlockHtmlToInline = (html) => {
			const template = document.createElement('template');
			template.innerHTML = String(html || '');
			const blockTags = new Set(['P', 'DIV', 'LI', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'BLOCKQUOTE', 'SECTION', 'ARTICLE', 'TR', 'UL', 'OL', 'ASIDE', 'DETAILS', 'SUMMARY']);

			const getDepth = (node) => {
				let depth = 0;
				let current = node;
				while (current.parentNode) {
					depth += 1;
					current = current.parentNode;
				}
				return depth;
			};

			while (true) {
				const blocks = Array.from(template.content.querySelectorAll('*'))
					.filter((element) => blockTags.has(element.tagName))
					.sort((left, right) => getDepth(right) - getDepth(left));

				if (blocks.length === 0) {
					break;
				}

				const element = blocks[0];
				if (element.nextSibling) {
					element.after(document.createElement('br'));
				}
				element.replaceWith(...element.childNodes);
			}

			return sanitizeInlineHtml(template.innerHTML)
				.replace(/(?:<br\s*\/?>\s*)+$/gi, '')
				.replace(/^(?:<br\s*\/?>\s*)+/gi, '')
				.replace(/(?:<br\s*\/?>\s*){3,}/gi, '<br><br>');
		};

		const parseTableElement = (table) => {
			const captionEl = table.querySelector('caption');
			const rows = [];
			const cellAlign = {};

			table.querySelectorAll('tr').forEach((tr, rowIndex) => {
				const cells = [];
				tr.querySelectorAll('th, td').forEach((cell, cellIndex) => {
					if (cell.classList.contains('blog-editor-table-block__insert')) {
						return;
					}

					cells.push(flattenBlockHtmlToInline(cell.innerHTML));
					const align = {};
					if (cell.classList.contains('blog-table-cell_h-center')) {
						align.h = 'center';
					}
					if (cell.classList.contains('blog-table-cell_v-middle')) {
						align.v = 'middle';
					}
					if (align.h || align.v) {
						cellAlign[rowIndex + '-' + cellIndex] = align;
					}
				});
				if (cells.length > 0) {
					rows.push(cells);
				}
			});

			return {
				withHead: table.querySelector('th') !== null,
				caption: captionEl ? String(captionEl.textContent || '').trim() : '',
				alignH: table.classList.contains('blog-table_h-center') ? 'center' : 'left',
				alignV: table.classList.contains('blog-table_v-middle') ? 'middle' : 'top',
				cellAlign: cellAlign,
				rows: rows
			};
		};

		const clipboardToInlineHtml = (clipboardData) => {
			const html = clipboardData.getData('text/html');
			if (html) {
				return flattenBlockHtmlToInline(html);
			}

			return escapeHtml(clipboardData.getData('text/plain') || '').replace(/\r\n|\r|\n/g, '<br>');
		};

		const insertInlineHtml = (html) => {
			if (document.queryCommandSupported && document.queryCommandSupported('insertHTML')) {
				document.execCommand('insertHTML', false, html);
				return;
			}

			const selection = window.getSelection();
			if (!selection || selection.rangeCount === 0) {
				return;
			}

			const range = selection.getRangeAt(0);
			range.deleteContents();
			const template = document.createElement('template');
			template.innerHTML = html;
			const fragment = template.content;
			const lastNode = fragment.lastChild;
			range.insertNode(fragment);

			if (lastNode) {
				range.setStartAfter(lastNode);
				range.collapse(true);
				selection.removeAllRanges();
				selection.addRange(range);
			}
		};

		const sanitizeInlineHtml = (html) => {
			const template = document.createElement('template');
			template.innerHTML = String(html || '');
			const allowedTags = new Set(['A', 'B', 'BR', 'CODE', 'I', 'MARK', 'S', 'SPAN', 'STRONG', 'SUB', 'SUP', 'U', 'EM', 'WBR']);
			const allowedSpanClasses = new Set(['blog-wrap', 'blog-nowrap']);

			template.content.querySelectorAll('*').forEach((element) => {
				if (!allowedTags.has(element.tagName)) {
					element.replaceWith(...element.childNodes);
					return;
				}

				Array.from(element.attributes).forEach((attribute) => {
					if (element.tagName === 'A' && ['href', 'target', 'rel'].includes(attribute.name)) {
						return;
					}

					if (element.tagName === 'SPAN' && attribute.name === 'class') {
						const className = Array.from(element.classList)
							.filter((name) => allowedSpanClasses.has(name))
							.join(' ');
						if (className) {
							element.setAttribute('class', className);
						} else {
							element.removeAttribute('class');
						}
						return;
					}

					if (element.tagName === 'SPAN' && attribute.name === 'style') {
						const color = element.style.color;
						const backgroundColor = element.style.backgroundColor;
						const whiteSpace = element.style.whiteSpace === 'nowrap' ? 'nowrap' : '';
						element.removeAttribute('style');
						if (color) {
							element.style.color = color;
						}
						if (backgroundColor) {
							element.style.backgroundColor = backgroundColor;
						}
						if (whiteSpace) {
							element.style.whiteSpace = whiteSpace;
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
					let level = Number(tagName.slice(1));
					if (level === 1) {
						level = 3;
					}

					blocks.push({
						type: 'header',
						data: {
							text: element.innerHTML,
							level: level
						}
					});
					return;
				}

				if (tagName === 'ul' || tagName === 'ol') {
					const items = Array.from(element.children)
						.filter((child) => child.tagName.toLowerCase() === 'li')
						.map((item) => flattenBlockHtmlToInline(item.innerHTML))
						.filter((itemHtml) => String(itemHtml || '').replace(/<br\s*\/?>/gi, '').trim() !== '');

					blocks.push({
						type: 'list',
						data: {
							style: tagName === 'ol' ? 'ordered' : 'unordered',
							items: items.length > 0 ? items : ['']
						}
					});
					return;
				}

				if (tagName === 'table') {
					blocks.push({
						type: 'table',
						data: parseTableElement(element)
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
							text: flattenBlockHtmlToInline(element.innerHTML),
							caption: '',
							alignment: 'left'
						}
					});
					return;
				}

				if (tagName === 'aside') {
					const style = element.classList.contains('blog-alert_warning')
						? 'warning'
						: (element.classList.contains('blog-alert_danger')
							? 'danger'
							: (element.classList.contains('blog-alert_success')
								? 'success'
								: ((element.classList.contains('blog-alert') || element.classList.contains('blog-alert_info')) ? 'info' : '')));
					if (style !== '') {
						blocks.push({
							type: 'alert',
							data: {
								style: style,
								text: flattenBlockHtmlToInline(element.innerHTML)
							}
						});
						return;
					}
				}

				if (tagName === 'details') {
					const summary = element.querySelector(':scope > summary');
					const body = element.cloneNode(true);
					const clonedSummary = body.querySelector('summary');
					if (clonedSummary) {
						clonedSummary.remove();
					}

					blocks.push({
						type: 'spoiler',
						data: {
							title: summary ? flattenBlockHtmlToInline(summary.innerHTML) : '',
							text: flattenBlockHtmlToInline(body.innerHTML),
							opened: element.hasAttribute('open')
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
				let level = Number(data.level);
				if (level === 1 || ![2, 3, 4, 5, 6].includes(level)) {
					level = 3;
				}
				return `<h${level}>${sanitizeInlineHtml(data.text)}</h${level}>`;
			}

			if (block.type === 'list') {
				const tag = data.style === 'ordered' ? 'ol' : 'ul';
				const items = Array.isArray(data.items) ? data.items : [];
				const listItems = items
					.map((item) => {
						const content = typeof item === 'string'
							? item
							: (item && typeof item.content === 'string' ? item.content : '');
						return `<li>${sanitizeInlineHtml(flattenBlockHtmlToInline(content))}</li>`;
					})
					.join('');

				return listItems !== '' ? `<${tag}>${listItems}</${tag}>` : '';
			}

			if (block.type === 'image') {
				const url = data.file && data.file.url ? String(data.file.url) : '';
				if (!url) {
					return '';
				}
				return `<img src="${escapeHtml(url)}" alt="${escapeHtml(data.caption || '')}">`;
			}

			if (block.type === 'quote') {
				return `<blockquote>${sanitizeInlineHtml(flattenBlockHtmlToInline(data.text))}</blockquote>`;
			}

			if (block.type === 'alert' || block.type === 'warning' || block.type === 'danger' || block.type === 'success') {
				const variant = ['info', 'warning', 'danger', 'success'].includes(data.style)
					? data.style
					: (block.type === 'alert' ? 'info' : block.type);
				const text = sanitizeInlineHtml(flattenBlockHtmlToInline(data.text));
				if (text === '') {
					return '';
				}
				return `<aside class="blog-alert blog-alert_${variant}">${text}</aside>`;
			}

			if (block.type === 'spoiler') {
				const title = sanitizeInlineHtml(flattenBlockHtmlToInline(data.title));
				const text = sanitizeInlineHtml(flattenBlockHtmlToInline(data.text));
				if (title === '' && text === '') {
					return '';
				}
				const openAttr = data.opened ? ' open="open"' : '';
				return `<details class="blog-spoiler"${openAttr}><summary>${title !== '' ? title : 'Подробнее'}</summary><p>${text}</p></details>`;
			}

			if (block.type === 'table') {
				const rows = Array.isArray(data.rows) ? data.rows : [];
				if (rows.length === 0) {
					return '';
				}

				const withHead = data.withHead !== false;
				const cellAlign = data.cellAlign && typeof data.cellAlign === 'object' ? data.cellAlign : {};
				const tableClasses = ['blog-table'];
				if (data.alignH === 'center') {
					tableClasses.push('blog-table_h-center');
				}
				if (data.alignV === 'middle') {
					tableClasses.push('blog-table_v-middle');
				}

				const renderCell = (cellHtml, rowIndex, cellIndex, cellTag) => {
					const align = cellAlign[rowIndex + '-' + cellIndex] || {};
					const cellClasses = [];
					if (align.h === 'center') {
						cellClasses.push('blog-table-cell_h-center');
					}
					if (align.v === 'middle') {
						cellClasses.push('blog-table-cell_v-middle');
					}
					const classAttr = cellClasses.length > 0 ? ' class="' + cellClasses.join(' ') + '"' : '';
					return '<' + cellTag + classAttr + '>' + sanitizeInlineHtml(flattenBlockHtmlToInline(cellHtml)) + '</' + cellTag + '>';
				};

				const renderRow = (row, rowIndex, cellTag) => {
					const cells = Array.isArray(row) ? row : [];
					return '<tr>' + cells.map((cell, cellIndex) => renderCell(cell, rowIndex, cellIndex, cellTag)).join('') + '</tr>';
				};

				const caption = String(data.caption || '').trim();
				const captionHtml = caption !== '' ? '<caption>' + escapeHtml(caption) + '</caption>' : '';
				let bodyHtml = '';

				if (withHead) {
					const headHtml = renderRow(rows[0] || [], 0, 'th');
					bodyHtml = rows.slice(1).map((row, index) => renderRow(row, index + 1, 'td')).join('');
					return '<table class="' + tableClasses.join(' ') + '">' + captionHtml + '<thead>' + headHtml + '</thead><tbody>' + bodyHtml + '</tbody></table>';
				}

				bodyHtml = rows.map((row, index) => renderRow(row, index, 'td')).join('');
				return '<table class="' + tableClasses.join(' ') + '">' + captionHtml + '<tbody>' + bodyHtml + '</tbody></table>';
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

		const inlineTools = ['bold', 'italic', 'link', 'underline', 'strike', 'superscript', 'subscript', 'textColor', 'markerColor', 'wrapMark', 'pinText'];
		const editor = new EditorJS({
			holder: editorRoot,
			data: {
				blocks: htmlToBlocks(initialHtml)
			},
			tunes: ['copy'],
			inlineToolbar: inlineTools,
			tools: {
				copy: CopyBlockTune,
				wrapMark: WrapMarkTool,
				pinText: PinTextTool,
				header: {
					class: Header,
					inlineToolbar: inlineTools,
					config: {
						levels: [2, 3, 4, 5, 6],
						defaultLevel: 3
					}
				},
				list: {
					class: BlogList,
					inlineToolbar: inlineTools,
					config: {
						defaultStyle: 'unordered'
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
					inlineToolbar: inlineTools
				},
				alert: {
					class: AlertBlockTool,
					inlineToolbar: inlineTools
				},
				spoiler: {
					class: SpoilerBlockTool,
					inlineToolbar: inlineTools
				},
				table: TableBlockTool,
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
				superscript: SuperscriptTool,
				subscript: SubscriptTool,
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
			},
			onReady: () => {
				editorRoot.querySelectorAll('.blog-wrap').forEach((mark) => {
					mark.contentEditable = 'false';
				});
			}
		});

		document.addEventListener('paste', (event) => {
			const target = event.target instanceof Element ? event.target : null;
			if (!target || !editorRoot.contains(target)) {
				return;
			}

			const inList = Boolean(target.closest('.cdx-list'));
			const inQuote = Boolean(target.closest('.cdx-quote'));
			const inAlert = Boolean(target.closest('.blog-editor-alert-block'));
			const inSpoiler = Boolean(target.closest('.blog-editor-spoiler-block'));
			if (!inList && !inQuote && !inAlert && !inSpoiler) {
				return;
			}

			if (!event.clipboardData) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			if (typeof event.stopImmediatePropagation === 'function') {
				event.stopImmediatePropagation();
			}

			insertInlineHtml(clipboardToInlineHtml(event.clipboardData));
		}, true);

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
