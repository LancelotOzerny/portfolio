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

			constructor({ data, api }) {
				this.api = api;
				this.data = this.normalizeData(data);
				this.wrapper = null;
			}

			normalizeData(data) {
				const source = data && typeof data === 'object' ? data : {};
				const rows = Array.isArray(source.rows)
					? source.rows
						.map((row) => Array.isArray(row) ? row.map((cell) => String(cell ?? '')) : null)
						.filter((row) => row !== null && row.length > 0)
					: [];

				if (rows.length === 0) {
					return {
						withHead: true,
						rows: [['', ''], ['', '']]
					};
				}

				const columnCount = Math.max(...rows.map((row) => row.length), 1);
				return {
					withHead: source.withHead !== false,
					rows: rows.map((row) => {
						const normalized = row.slice(0, columnCount);
						while (normalized.length < columnCount) {
							normalized.push('');
						}
						return normalized;
					})
				};
			}

			render() {
				this.wrapper = document.createElement('div');
				this.wrapper.className = 'blog-editor-table-block';
				this.draw();
				return this.wrapper;
			}

			draw() {
				if (!this.wrapper) {
					return;
				}

				this.wrapper.innerHTML = '';

				const table = document.createElement('table');
				table.className = 'blog-editor-table-block__table';

				this.data.rows.forEach((row, rowIndex) => {
					const tr = document.createElement('tr');
					row.forEach((cellHtml, cellIndex) => {
						const cell = document.createElement(this.data.withHead && rowIndex === 0 ? 'th' : 'td');
						cell.contentEditable = 'true';
						cell.innerHTML = cellHtml;
						cell.dataset.row = String(rowIndex);
						cell.dataset.col = String(cellIndex);
						tr.appendChild(cell);
					});
					table.appendChild(tr);
				});

				this.wrapper.appendChild(table);
			}

			renderSettings() {
				const wrapper = document.createElement('div');

				const createButton = (label, onClick) => {
					const button = document.createElement('div');
					button.className = this.api.styles.settingsButton;
					button.textContent = label;
					button.addEventListener('click', () => {
						this.readFromDom();
						onClick();
						this.draw();
					});
					wrapper.appendChild(button);
				};

				createButton('+ строка', () => {
					const columns = this.data.rows[0] ? this.data.rows[0].length : 1;
					this.data.rows.push(Array.from({ length: columns }, () => ''));
				});
				createButton('− строка', () => {
					if (this.data.rows.length > 1) {
						this.data.rows.pop();
					}
				});
				createButton('+ столбец', () => {
					this.data.rows.forEach((row) => row.push(''));
				});
				createButton('− столбец', () => {
					if ((this.data.rows[0] || []).length > 1) {
						this.data.rows.forEach((row) => row.pop());
					}
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

				const rows = [];
				this.wrapper.querySelectorAll('tr').forEach((tr) => {
					const cells = [];
					tr.querySelectorAll('th, td').forEach((cell) => {
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
					rows: this.data.rows
				};
			}

			onPaste(event) {
				const table = event.detail && event.detail.data ? event.detail.data : null;
				if (!(table instanceof HTMLElement) || table.tagName !== 'TABLE') {
					return;
				}

				const rows = [];
				table.querySelectorAll('tr').forEach((tr) => {
					const cells = [];
					tr.querySelectorAll('th, td').forEach((cell) => {
						cells.push(flattenBlockHtmlToInline(cell.innerHTML));
					});
					if (cells.length > 0) {
						rows.push(cells);
					}
				});

				this.data = this.normalizeData({
					withHead: table.querySelector('th') !== null,
					rows: rows
				});
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
			const blockTags = new Set(['P', 'DIV', 'LI', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'BLOCKQUOTE', 'SECTION', 'ARTICLE', 'TR', 'UL', 'OL']);

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
			const allowedTags = new Set(['A', 'B', 'BR', 'CODE', 'I', 'MARK', 'S', 'SPAN', 'STRONG', 'SUB', 'SUP', 'U', 'EM']);

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
					const rows = [];
					element.querySelectorAll('tr').forEach((tr) => {
						const cells = [];
						tr.querySelectorAll('th, td').forEach((cell) => {
							cells.push(flattenBlockHtmlToInline(cell.innerHTML));
						});
						if (cells.length > 0) {
							rows.push(cells);
						}
					});

					blocks.push({
						type: 'table',
						data: {
							withHead: element.querySelector('th') !== null,
							rows: rows.length > 0 ? rows : [['', ''], ['', '']]
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
							text: flattenBlockHtmlToInline(element.innerHTML),
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

			if (block.type === 'table') {
				const rows = Array.isArray(data.rows) ? data.rows : [];
				if (rows.length === 0) {
					return '';
				}

				const withHead = data.withHead !== false;
				const renderRow = (row, cellTag) => {
					const cells = Array.isArray(row) ? row : [];
					return `<tr>${cells.map((cell) => `<${cellTag}>${sanitizeInlineHtml(flattenBlockHtmlToInline(cell))}</${cellTag}>`).join('')}</tr>`;
				};

				if (withHead) {
					const headHtml = renderRow(rows[0] || [], 'th');
					const bodyHtml = rows.slice(1).map((row) => renderRow(row, 'td')).join('');
					return `<table><thead>${headHtml}</thead><tbody>${bodyHtml}</tbody></table>`;
				}

				return `<table><tbody>${rows.map((row) => renderRow(row, 'td')).join('')}</tbody></table>`;
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
			inlineToolbar: ['bold', 'italic', 'link', 'underline', 'strike', 'superscript', 'subscript', 'textColor', 'markerColor'],
			tools: {
				header: {
					class: Header,
					inlineToolbar: ['bold', 'italic', 'link', 'underline', 'strike', 'superscript', 'subscript', 'textColor', 'markerColor'],
					config: {
						levels: [2, 3, 4, 5, 6],
						defaultLevel: 3
					}
				},
				list: {
					class: List,
					inlineToolbar: ['bold', 'italic', 'link', 'underline', 'strike', 'superscript', 'subscript', 'textColor', 'markerColor'],
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
					inlineToolbar: ['bold', 'italic', 'link', 'underline', 'strike', 'superscript', 'subscript', 'textColor', 'markerColor']
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
			}
		});

		document.addEventListener('paste', (event) => {
			const target = event.target instanceof Element ? event.target : null;
			if (!target || !editorRoot.contains(target)) {
				return;
			}

			const inList = Boolean(target.closest('.cdx-list'));
			const inQuote = Boolean(target.closest('.cdx-quote'));
			if (!inList && !inQuote) {
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
