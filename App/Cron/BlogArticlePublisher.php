<?php

namespace App\Cron;

use App\Services\Blog\BlogArticlePublicationService;

final class BlogArticlePublisher
{
	public function run(): void
	{
		(new BlogArticlePublicationService())->publishDueArticles();
	}
}
