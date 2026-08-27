<?php

namespace App\Cron;

use App\Services\Sitemap\SitemapGenerator;

final class SitemapBuilder
{
	public function run(): void
	{
		(new SitemapGenerator())->generate();
	}
}
