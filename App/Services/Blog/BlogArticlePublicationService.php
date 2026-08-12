<?php

namespace App\Services\Blog;

use Models\BlogArticlesModel;
use Throwable;

final class BlogArticlePublicationService
{
	public function publishNow(int $articleId): bool
	{
		if ($articleId <= 0) {
			return false;
		}

		return (new BlogArticlesModel())->publishNow($articleId);
	}

	public function schedule(int $articleId, string $publishedAt): bool
	{
		$publishedAt = $this->normalizeDatetime($publishedAt);
		if ($articleId <= 0 || $publishedAt === '') {
			return false;
		}

		$timestamp = strtotime($publishedAt);
		if ($timestamp === false) {
			return false;
		}

		if ($timestamp <= time()) {
			return $this->publishNow($articleId);
		}

		return (new BlogArticlesModel())->schedulePublication($articleId, date('Y-m-d H:i:s', $timestamp));
	}

	public function publishDueArticles(): int
	{
		$model = new BlogArticlesModel();
		$published = 0;

		try {
			$articles = $model->findDueForPublication();
		} catch (Throwable) {
			return 0;
		}

		foreach ($articles as $article) {
			$articleId = (int) ($article->id ?? 0);
			if ($articleId <= 0) {
				continue;
			}

			if ($model->activateScheduled($articleId)) {
				$published++;
			}
		}

		return $published;
	}

	public function isPublished(object $article): bool
	{
		return (int) ($article->enabled ?? 0) === 1;
	}

	public function getPublicationDatetime(object $article): ?string
	{
		if (!$this->isPublished($article)) {
			return null;
		}

		$publishedAt = trim((string) ($article->published_at ?? ''));
		if ($publishedAt !== '') {
			return $publishedAt;
		}

		$createdAt = trim((string) ($article->created_at ?? ''));

		return $createdAt !== '' ? $createdAt : null;
	}

	public function getScheduledDatetime(object $article): ?string
	{
		if ($this->isPublished($article)) {
			return null;
		}

		$publishedAt = trim((string) ($article->published_at ?? ''));
		if ($publishedAt === '') {
			return null;
		}

		$timestamp = strtotime($publishedAt);
		if ($timestamp === false || $timestamp <= time()) {
			return null;
		}

		return $publishedAt;
	}

	public function formatForInput(?string $value): string
	{
		$value = trim((string) $value);
		if ($value === '') {
			return '';
		}

		$timestamp = strtotime($value);
		if ($timestamp === false) {
			return '';
		}

		return date('Y-m-d\TH:i', $timestamp);
	}

	private function normalizeDatetime(string $value): string
	{
		$value = trim(str_replace('T', ' ', $value));
		if ($value === '') {
			return '';
		}

		$timestamp = strtotime($value);
		if ($timestamp === false) {
			return '';
		}

		return date('Y-m-d H:i:s', $timestamp);
	}
}
