<?php

namespace App\Services\Blog;

use App\Services\ApiToken\ApiTokenAuth;
use App\Services\Auth\RoleCode;
use App\Services\Auth\RoleLevels;
use Models\BlogArticlesModel;
use Models\BlogTopicsModel;
use Throwable;

final class BlogApiService
{
	public function __construct(
		private readonly BlogTopicsModel $topicsModel = new BlogTopicsModel(),
		private readonly BlogArticlesModel $articlesModel = new BlogArticlesModel(),
		private readonly BlogSeoService $seoService = new BlogSeoService(),
		private readonly BlogArticlePublicationService $publicationService = new BlogArticlePublicationService(),
		private readonly SymbolicCodeService $codeService = new SymbolicCodeService(),
		private readonly RoleLevels $roleLevels = new RoleLevels(),
		private readonly ApiTokenAuth $apiAuth = new ApiTokenAuth(),
	) {
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	public function listTopics(): array
	{
		$topics = $this->canSeeDrafts()
			? $this->topicsModel->findAll()
			: $this->topicsModel->findEnabled();

		$items = [];
		foreach ($topics as $topic) {
			$items[] = $this->mapTopic($topic);
		}

		return $items;
	}

	/**
	 * @return list<array<string, mixed>>|null
	 */
	public function listTopicArticles(string $topicSegment): ?array
	{
		$topic = $this->resolveAccessibleTopic($topicSegment);
		if ($topic === null) {
			return null;
		}

		$items = [];
		foreach ($this->articlesModel->findByTopicId((int) $topic->id, !$this->canSeeDrafts()) as $article) {
			$items[] = $this->mapArticleBrief($article);
		}

		return $items;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function getArticle(string $topicSegment, string $articleSegment): ?array
	{
		$topic = $this->resolveAccessibleTopic($topicSegment);
		if ($topic === null) {
			return null;
		}

		foreach ($this->articlesModel->findByTopicId((int) $topic->id, !$this->canSeeDrafts()) as $article) {
			if ($this->matchesArticle($article, $articleSegment)) {
				return $this->mapArticleDetail($article);
			}
		}

		return null;
	}

	private function resolveAccessibleTopic(string $segment): ?object
	{
		$topic = $this->resolveTopic($segment);
		if ($topic === null) {
			return null;
		}

		if ($this->canSeeDrafts() || (int) ($topic->enabled ?? 0) === 1) {
			return $topic;
		}

		return null;
	}

	private function resolveTopic(string $segment): ?object
	{
		$segment = trim($segment);
		if ($segment === '') {
			return null;
		}

		try {
			if (ctype_digit($segment)) {
				$topic = $this->topicsModel->findById((int) $segment);
				if ($topic !== null) {
					return $topic;
				}
			}

			return $this->topicsModel->findByCode($segment);
		} catch (Throwable) {
			return null;
		}
	}

	private function matchesArticle(object $article, string $segment): bool
	{
		$segment = trim($segment);
		if ($segment === '') {
			return false;
		}

		$articleId = (int) ($article->id ?? 0);
		$code = trim((string) ($article->code ?? ''));
		$publicCode = $this->codeService->resolvePublicSegment($code, $articleId);

		if ($publicCode === $segment || ($code !== '' && $code === $segment)) {
			return true;
		}

		return ctype_digit($segment) && (string) $articleId === $segment;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function mapTopic(object $topic): array
	{
		$topicId = (int) ($topic->id ?? 0);
		$isDraft = (int) ($topic->enabled ?? 0) !== 1;

		return [
			'id' => $topicId,
			'created_at' => (string) ($topic->created_at ?? ''),
			'title' => (string) ($topic->title ?? ''),
			'code' => $this->codeService->resolvePublicSegment((string) ($topic->code ?? ''), $topicId),
			'detail_text' => (string) ($topic->detail_text ?? ''),
			'preview_text' => (string) ($topic->preview_text ?? ''),
			'status' => $isDraft ? 'draft' : 'published',
			'seo' => $this->seoService->getFormData(BlogSeoService::TYPE_TOPIC, (string) $topicId),
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function mapArticleBrief(object $article): array
	{
		$articleId = (int) ($article->id ?? 0);
		$isDraft = !$this->publicationService->isPublished($article);

		return [
			'id' => $articleId,
			'title' => (string) ($article->title ?? ''),
			'preview_text' => (string) ($article->preview_text ?? ''),
			'code' => $this->codeService->resolvePublicSegment((string) ($article->code ?? ''), $articleId),
			'published_at' => $this->publicationService->getPublicationDatetime($article),
			'status' => $isDraft ? 'draft' : 'published',
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function mapArticleDetail(object $article): array
	{
		$articleId = (int) ($article->id ?? 0);
		$payload = $this->mapArticleBrief($article);
		$payload['detail_text'] = (string) ($article->detail_text ?? '');
		$payload['seo'] = $this->seoService->getFormData(BlogSeoService::TYPE_ARTICLE, (string) $articleId);

		return $payload;
	}

	private function canSeeDrafts(): bool
	{
		$code = $this->currentRoleCode();
		if ($code === '') {
			return false;
		}

		if ($code === RoleCode::AI_AGENT || $code === RoleCode::ADMIN) {
			return true;
		}

		$adminLevel = $this->roleLevels->getLevel(RoleCode::ADMIN);

		return $adminLevel > 0 && $this->roleLevels->getLevel($code) >= $adminLevel;
	}

	private function currentRoleCode(): string
	{
		$user = $this->apiAuth->resolveUser();
		if ($user === null) {
			return '';
		}

		return strtolower(trim((string) ($user->role_code ?? '')));
	}
}
