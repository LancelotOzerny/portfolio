<?php

namespace App\Services\Blog;

use Models\BlogArticlesModel;
use Throwable;

class ArticleRatingService
{
	public const MAX_RATING = 5;

	private BlogArticlesModel $articlesModel;
	private BlogViewerIdentity $viewerIdentity;

	public function __construct(
		?BlogArticlesModel $articlesModel = null,
		?BlogViewerIdentity $viewerIdentity = null
	) {
		$this->articlesModel = $articlesModel ?? new BlogArticlesModel();
		$this->viewerIdentity = $viewerIdentity ?? new BlogViewerIdentity();
	}

	/**
	 * @return array{average: float, count: int, user_rating: int|null, can_vote: bool}
	 */
	public function getState(int $articleId): array
	{
		$summary = $this->safeSummary($articleId);
		$userRating = $this->safeUserRating($articleId);

		return [
			'average' => $summary['average'],
			'count' => $summary['count'],
			'user_rating' => $userRating,
			'can_vote' => true,
		];
	}

	/**
	 * @return array{success: bool, message: string, average: float, count: int, user_rating: int|null, can_vote: bool}
	 */
	public function vote(int $articleId, int $rating): array
	{
		$state = $this->getState($articleId);

		if ($articleId <= 0) {
			return $this->response(false, 'Статья не найдена.', $state);
		}

		if ($rating < 1 || $rating > self::MAX_RATING) {
			return $this->response(false, 'Оценка должна быть от 1 до 5.', $state);
		}

		$viewerKey = $this->viewerIdentity->resolveKey();
		if ($viewerKey === '') {
			return $this->response(false, 'Не удалось определить пользователя.', $state);
		}

		$hadVote = $state['user_rating'] !== null;

		try {
			$saved = $this->articlesModel->saveViewerRating(
				$articleId,
				$viewerKey,
				$rating,
				$this->viewerIdentity->resolveClientIp()
			);
		} catch (Throwable) {
			return $this->response(false, 'Не удалось сохранить оценку.', $state);
		}

		if (!$saved) {
			return $this->response(false, 'Не удалось сохранить оценку.', $this->getState($articleId));
		}

		$state = $this->getState($articleId);
		$message = $hadVote ? 'Оценка обновлена.' : 'Спасибо за оценку!';

		return $this->response(true, $message, $state);
	}

	/**
	 * @return array{average: float, count: int}
	 */
	private function safeSummary(int $articleId): array
	{
		try {
			return $this->articlesModel->getRatingSummary($articleId);
		} catch (Throwable) {
			return [
				'average' => 0.0,
				'count' => 0,
			];
		}
	}

	private function safeUserRating(int $articleId): ?int
	{
		$viewerKey = $this->viewerIdentity->resolveKey();
		if ($viewerKey === '') {
			return null;
		}

		try {
			return $this->articlesModel->findViewerRating($articleId, $viewerKey);
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * @param array{average: float, count: int, user_rating: int|null, can_vote: bool} $state
	 * @return array{success: bool, message: string, average: float, count: int, user_rating: int|null, can_vote: bool}
	 */
	private function response(bool $success, string $message, array $state): array
	{
		return [
			'success' => $success,
			'message' => $message,
			'average' => $state['average'],
			'count' => $state['count'],
			'user_rating' => $state['user_rating'],
			'can_vote' => $state['can_vote'],
		];
	}
}
