<?php

namespace App\Services\Blog;

use Models\BlogArticleCommentsModel;
use Throwable;

class ArticleCommentService
{
	private const MAX_AUTHOR_LENGTH = 80;
	private const MAX_TEXT_LENGTH = 500;
	private const DEFAULT_AVATAR_PATH = '/Templates/Light/img/avatar-default.png';

	private BlogArticleCommentsModel $commentsModel;
	private BlogViewerIdentity $viewerIdentity;
	private BlogDateFormatter $dateFormatter;

	public function __construct(
		?BlogArticleCommentsModel $commentsModel = null,
		?BlogViewerIdentity $viewerIdentity = null,
		?BlogDateFormatter $dateFormatter = null
	) {
		$this->commentsModel = $commentsModel ?? new BlogArticleCommentsModel();
		$this->viewerIdentity = $viewerIdentity ?? new BlogViewerIdentity();
		$this->dateFormatter = $dateFormatter ?? new BlogDateFormatter();
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	public function getTreeForArticle(int $articleId): array
	{
		if ($articleId <= 0) {
			return [];
		}

		try {
			$rows = $this->commentsModel->findByArticleId($articleId);
		} catch (Throwable) {
			return [];
		}

		if ($rows === []) {
			return [];
		}

		$ids = [];
		foreach ($rows as $row) {
			$id = (int) ($row->id ?? 0);
			if ($id > 0) {
				$ids[] = $id;
			}
		}

		$viewerKey = $this->viewerIdentity->resolveKey();

		try {
			$summaries = $this->commentsModel->getVoteSummariesByCommentIds($ids);
		} catch (Throwable) {
			$summaries = [];
		}

		try {
			$userVotes = $viewerKey !== ''
				? $this->commentsModel->findViewerVotesByCommentIds($ids, $viewerKey)
				: [];
		} catch (Throwable) {
			$userVotes = [];
		}

		$items = [];
		foreach ($rows as $row) {
			$id = (int) ($row->id ?? 0);
			if ($id <= 0) {
				continue;
			}

			$parentId = isset($row->parent_id) && $row->parent_id !== null
				? (int) $row->parent_id
				: 0;

			$summary = $summaries[$id] ?? ['likes' => 0, 'dislikes' => 0];

			$items[$id] = [
				'id' => $id,
				'parent_id' => $parentId > 0 ? $parentId : null,
				'author' => trim((string) ($row->updated_by_name ?? '')) !== ''
					? (string) $row->updated_by_name
					: 'Аноним',
				'avatar' => self::DEFAULT_AVATAR_PATH,
				'text' => (string) ($row->comment_text ?? ''),
				'date' => $this->dateFormatter->format((string) ($row->created_at ?? '')),
				'likes' => (int) ($summary['likes'] ?? 0),
				'dislikes' => (int) ($summary['dislikes'] ?? 0),
				'user_vote' => $userVotes[$id] ?? null,
				'replies' => [],
			];
		}

		foreach ($items as $id => $item) {
			$parentId = $item['parent_id'];
			if ($parentId === null || !isset($items[$parentId])) {
				continue;
			}

			// Не глубже второго уровня: ответ на ответ вешаем на корневой комментарий.
			$rootParentId = $parentId;
			$guard = 0;
			while (
				isset($items[$rootParentId])
				&& $items[$rootParentId]['parent_id'] !== null
				&& isset($items[$items[$rootParentId]['parent_id']])
				&& $guard < 20
			) {
				$rootParentId = (int) $items[$rootParentId]['parent_id'];
				$guard++;
			}

			$items[$id]['parent_id'] = $rootParentId;
		}

		$childrenMap = [];
		$rootIds = [];

		foreach ($items as $id => $item) {
			$parentId = $item['parent_id'];
			if ($parentId !== null && isset($items[$parentId]) && $items[$parentId]['parent_id'] === null) {
				$childrenMap[$parentId][] = $id;
				continue;
			}

			$rootIds[] = $id;
		}

		$buildNode = function (int $id) use (&$buildNode, &$items, &$childrenMap): array {
			$node = $items[$id];
			$node['parent_id'] = $node['parent_id'] !== null && isset($items[$node['parent_id']])
				? $node['parent_id']
				: null;
			$node['replies'] = [];

			foreach ($childrenMap[$id] ?? [] as $childId) {
				$node['replies'][] = $buildNode($childId);
			}

			return $node;
		};

		$tree = [];
		foreach ($rootIds as $rootId) {
			$tree[] = $buildNode($rootId);
		}

		return $tree;
	}

	/**
	 * @return array{success: bool, message: string, comment?: array<string, mixed>, comments?: list<array<string, mixed>>}
	 */
	public function addComment(int $articleId, string $authorName, string $commentText, ?int $parentId = null): array
	{
		if ($articleId <= 0) {
			return ['success' => false, 'message' => 'Статья не найдена.'];
		}

		$authorName = trim($authorName);
		$commentText = trim($commentText);

		if ($authorName === '') {
			$authorName = 'Аноним';
		}

		if (mb_strlen($authorName) > self::MAX_AUTHOR_LENGTH) {
			return ['success' => false, 'message' => 'Имя слишком длинное.'];
		}

		if ($commentText === '') {
			return ['success' => false, 'message' => 'Введите текст комментария.'];
		}

		if ($parentId !== null && $parentId > 0) {
			try {
				if (!$this->commentsModel->belongsToArticle($parentId, $articleId)) {
					return ['success' => false, 'message' => 'Комментарий для ответа не найден.'];
				}

				$parentComment = $this->commentsModel->findById($parentId);
			} catch (Throwable) {
				return ['success' => false, 'message' => 'Не удалось проверить родительский комментарий.'];
			}

			if ($parentComment === null) {
				return ['success' => false, 'message' => 'Комментарий для ответа не найден.'];
			}

			$replyToName = trim((string) ($parentComment->updated_by_name ?? ''));
			if ($replyToName === '') {
				$replyToName = 'Аноним';
			}

			$storageParentId = $parentId;
			$grandParentId = isset($parentComment->parent_id) && $parentComment->parent_id !== null
				? (int) $parentComment->parent_id
				: 0;
			if ($grandParentId > 0) {
				$storageParentId = $grandParentId;
			}

			$parentId = $storageParentId;
			$commentText = $this->formatReplyText($replyToName, $commentText);
		} else {
			$parentId = null;
		}

		if (mb_strlen($commentText) > self::MAX_TEXT_LENGTH) {
			return ['success' => false, 'message' => 'Комментарий не должен превышать 500 символов.'];
		}

		$viewerKey = $this->viewerIdentity->resolveKey();

		try {
			$commentId = $this->commentsModel->createComment(
				$articleId,
				$authorName,
				$commentText,
				$parentId,
				$viewerKey
			);
		} catch (Throwable) {
			return ['success' => false, 'message' => 'Не удалось сохранить комментарий.'];
		}

		if ($commentId <= 0) {
			return ['success' => false, 'message' => 'Не удалось сохранить комментарий.'];
		}

		$comments = $this->getTreeForArticle($articleId);
		$created = $this->findCommentInTree($comments, $commentId);

		return [
			'success' => true,
			'message' => 'Комментарий добавлен.',
			'comment' => $created,
			'comments' => $comments,
		];
	}

	/**
	 * @return array{success: bool, message: string, likes?: int, dislikes?: int, user_vote?: int|null, comments?: list<array<string, mixed>>}
	 */
	public function vote(int $articleId, int $commentId, int $vote): array
	{
		if ($articleId <= 0 || $commentId <= 0) {
			return ['success' => false, 'message' => 'Комментарий не найден.'];
		}

		if ($vote !== 1 && $vote !== -1) {
			return ['success' => false, 'message' => 'Некорректный голос.'];
		}

		try {
			if (!$this->commentsModel->belongsToArticle($commentId, $articleId)) {
				return ['success' => false, 'message' => 'Комментарий не найден.'];
			}
		} catch (Throwable) {
			return ['success' => false, 'message' => 'Не удалось проверить комментарий.'];
		}

		$viewerKey = $this->viewerIdentity->resolveKey();
		if ($viewerKey === '') {
			return ['success' => false, 'message' => 'Не удалось определить пользователя.'];
		}

		try {
			$saved = $this->commentsModel->saveViewerVote($commentId, $viewerKey, $vote);
		} catch (Throwable) {
			return ['success' => false, 'message' => 'Не удалось сохранить голос.'];
		}

		if (!$saved) {
			return ['success' => false, 'message' => 'Не удалось сохранить голос.'];
		}

		$comments = $this->getTreeForArticle($articleId);
		$updated = $this->findCommentInTree($comments, $commentId);

		return [
			'success' => true,
			'message' => 'Голос сохранён.',
			'likes' => (int) ($updated['likes'] ?? 0),
			'dislikes' => (int) ($updated['dislikes'] ?? 0),
			'user_vote' => $updated['user_vote'] ?? null,
			'comments' => $comments,
		];
	}

	/**
	 * @param list<array<string, mixed>> $tree
	 * @return array<string, mixed>|null
	 */
	private function findCommentInTree(array $tree, int $commentId): ?array
	{
		foreach ($tree as $item) {
			if ((int) ($item['id'] ?? 0) === $commentId) {
				return $item;
			}

			$nested = $this->findCommentInTree($item['replies'] ?? [], $commentId);
			if ($nested !== null) {
				return $nested;
			}
		}

		return null;
	}

	private function formatReplyText(string $replyToName, string $commentText): string
	{
		$replyToName = trim($replyToName);
		$commentText = trim($commentText);
		if ($replyToName === '' || $commentText === '') {
			return $commentText;
		}

		$prefix = $replyToName . ', ';
		if (mb_stripos($commentText, $prefix) === 0) {
			return $commentText;
		}

		return $prefix . $commentText;
	}
}
