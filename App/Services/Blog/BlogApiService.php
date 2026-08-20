<?php

namespace App\Services\Blog;

use App\Services\ApiToken\ApiTokenAuth;
use App\Services\Auth\RoleCode;
use App\Services\Auth\RoleLevels;
use App\Services\ContentEditor\ContentEditorUploadService;
use App\Services\Seo\Config\SeoConfig;
use InvalidArgumentException;
use Models\BlogArticlesModel;
use Models\BlogTopicsModel;
use RuntimeException;
use Throwable;

final class BlogApiService
{
	private const ARTICLES_LIST_LIMIT = 100;

	public function __construct(
		private readonly BlogTopicsModel $topicsModel = new BlogTopicsModel(),
		private readonly BlogArticlesModel $articlesModel = new BlogArticlesModel(),
		private readonly BlogSeoService $seoService = new BlogSeoService(),
		private readonly BlogArticlePublicationService $publicationService = new BlogArticlePublicationService(),
		private readonly SymbolicCodeService $codeService = new SymbolicCodeService(),
		private readonly ArticleContentSanitizer $contentSanitizer = new ArticleContentSanitizer(),
		private readonly RoleLevels $roleLevels = new RoleLevels(),
		private readonly ApiTokenAuth $apiAuth = new ApiTokenAuth(),
		private readonly ContentEditorUploadService $uploadService = new ContentEditorUploadService(),
		private readonly SeoConfig $seoConfig = new SeoConfig(),
	) {
	}

	public function isAuthenticated(): bool
	{
		return $this->apiAuth->resolveUser() !== null;
	}

	public function canManage(): bool
	{
		$code = $this->currentRoleCode();
		if ($code === '') {
			return false;
		}

		if ($code === RoleCode::ADMIN) {
			return true;
		}

		return $this->roleLevels->isAtLeast($code, RoleCode::AI_AGENT);
	}

	public function canManageMedia(): bool
	{
		return $this->canManage();
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function createArticle(string $rubricSegment, string $title, string $code, string $previewText): ?array
	{
		$title = trim($title);
		$previewText = trim($previewText);

		if ($title === '') {
			throw new InvalidArgumentException('Укажите заголовок.');
		}

		if (mb_strlen($title) > 255) {
			throw new InvalidArgumentException('Заголовок должен быть не длиннее 255 символов.');
		}

		if (mb_strlen($previewText) > 500) {
			throw new InvalidArgumentException('Preview текст должен быть не длиннее 500 символов.');
		}

		$topic = $this->resolveTopic($rubricSegment);
		if ($topic === null) {
			return null;
		}

		$topicId = (int) ($topic->id ?? 0);
		if ($topicId <= 0) {
			return null;
		}

		$code = $this->codeService->normalize($code);
		if ($code === '') {
			$code = $this->codeService->fromTitle($title);
		}

		if (!$this->codeService->isValid($code)) {
			throw new InvalidArgumentException('Символьный код должен содержать только латинские буквы, цифры, "-" и "_".');
		}

		if ($this->articlesModel->isCodeTaken($code)) {
			throw new InvalidArgumentException('Символьный код уже используется.');
		}

		$articleId = $this->articlesModel->createForAdmin($topicId, $title, $code, $previewText);
		if ($articleId <= 0) {
			throw new RuntimeException('Не удалось создать статью.');
		}

		if (!$this->articlesModel->replaceTopicIds($articleId, [$topicId])) {
			throw new RuntimeException('Не удалось привязать статью к рубрике.');
		}

		$article = $this->articlesModel->findById($articleId);
		if ($article === null) {
			throw new RuntimeException('Статья создана, но не удалось получить данные.');
		}

		return $this->mapArticleBrief($article);
	}

	/**
	 * @param array<string, mixed> $input
	 * @return array<string, mixed>|null
	 */
	public function updateArticleSeo(string $articleSegment, array $input): ?array
	{
		$article = $this->findArticleBySegment($articleSegment);
		if ($article === null) {
			return null;
		}

		$articleId = (int) ($article->id ?? 0);

		return $this->seoService->saveFromApi(BlogSeoService::TYPE_ARTICLE, (string) $articleId, $input);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function updateArticlePreviewText(string $articleSegment, string $previewText): ?array
	{
		$previewText = trim($previewText);
		if (mb_strlen($previewText) > 500) {
			throw new InvalidArgumentException('Preview текст должен быть не длиннее 500 символов.');
		}

		$article = $this->findArticleBySegment($articleSegment);
		if ($article === null) {
			return null;
		}

		$articleId = (int) ($article->id ?? 0);
		if (!$this->articlesModel->updatePreviewText($articleId, $previewText)) {
			throw new RuntimeException('Не удалось сохранить preview текст.');
		}

		$updated = $this->articlesModel->findById($articleId);
		if ($updated === null) {
			throw new RuntimeException('Не удалось получить обновлённую статью.');
		}

		return $this->mapArticleBrief($updated);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function updateArticleDetailText(string $articleSegment, string $detailText): ?array
	{
		$article = $this->findArticleBySegment($articleSegment);
		if ($article === null) {
			return null;
		}

		$articleId = (int) ($article->id ?? 0);
		$detailText = $this->contentSanitizer->sanitize($detailText);
		if (!$this->articlesModel->updateDetailText($articleId, $detailText)) {
			throw new RuntimeException('Не удалось сохранить детальный текст.');
		}

		$updated = $this->articlesModel->findById($articleId);
		if ($updated === null) {
			throw new RuntimeException('Не удалось получить обновлённую статью.');
		}

		return $this->mapArticleDetail($updated);
	}

	public function uploadArticleMedia(string $articleCode, string $type, string $fileKey = 'file'): ?string
	{
		$type = strtolower(trim($type));
		if ($type !== 'preview' && $type !== 'detail') {
			throw new InvalidArgumentException('Поле type должно быть preview или detail.');
		}

		$article = $this->findArticleBySegment($articleCode);
		if ($article === null) {
			return null;
		}

		$articleId = (int) ($article->id ?? 0);
		if ($articleId <= 0) {
			return null;
		}

		$relativePath = $this->uploadService->saveImage(
			$articleId,
			$fileKey,
			'images/blog/articles',
			'blog_article_' . $type
		);

		if (!$this->articlesModel->updateImagePath($articleId, $type, $relativePath)) {
			throw new RuntimeException('Не удалось сохранить путь к изображению.');
		}

		return $this->absolutePublicUrl($relativePath);
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
	public function listArticles(?string $rubric = null): ?array
	{
		$onlyActive = !$this->canSeeDrafts();
		$rubric = trim((string) $rubric);

		if ($rubric === '') {
			return $this->mapArticleBriefs(
				$this->articlesModel->findLatest(self::ARTICLES_LIST_LIMIT, $onlyActive)
			);
		}

		$topic = $this->resolveAccessibleTopic($rubric);
		if ($topic === null) {
			return null;
		}

		$articles = $this->sortByLatest(
			$this->articlesModel->findByTopicId((int) $topic->id, $onlyActive)
		);

		return $this->mapArticleBriefs(array_slice($articles, 0, self::ARTICLES_LIST_LIMIT));
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
	 * @param list<object> $articles
	 * @return list<array<string, mixed>>
	 */
	private function mapArticleBriefs(array $articles): array
	{
		$items = [];
		foreach ($articles as $article) {
			$items[] = $this->mapArticleBrief($article);
		}

		return $items;
	}

	/**
	 * @param list<object> $articles
	 * @return list<object>
	 */
	private function sortByLatest(array $articles): array
	{
		usort($articles, static function (object $left, object $right): int {
			$leftCreated = strtotime((string) ($left->created_at ?? '')) ?: 0;
			$rightCreated = strtotime((string) ($right->created_at ?? '')) ?: 0;
			if ($leftCreated !== $rightCreated) {
				return $rightCreated <=> $leftCreated;
			}

			return (int) ($right->id ?? 0) <=> (int) ($left->id ?? 0);
		});

		return $articles;
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

	private function findArticleBySegment(string $articleCode): ?object
	{
		$articleCode = trim($articleCode);
		if ($articleCode === '') {
			return null;
		}

		$normalized = $this->codeService->normalize($articleCode);
		if ($normalized !== '') {
			$article = $this->articlesModel->findByCode($normalized);
			if ($article !== null) {
				return $article;
			}
		}

		$article = $this->articlesModel->findByCode($articleCode);
		if ($article !== null) {
			return $article;
		}

		if (ctype_digit($articleCode)) {
			return $this->articlesModel->findById((int) $articleCode);
		}

		return null;
	}

	private function absolutePublicUrl(string $path): string
	{
		$path = '/' . ltrim(str_replace('\\', '/', $path), '/');
		$domain = rtrim((string) ($this->seoConfig->getSite()['domain'] ?? ''), '/');
		if ($domain !== '') {
			return $domain . $path;
		}

		$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443');
		$host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
		if ($host === '') {
			return $path;
		}

		return ($https ? 'https' : 'http') . '://' . $host . $path;
	}
}
