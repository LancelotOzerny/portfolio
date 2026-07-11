<?php

namespace App\Services\Seo;

class SeoMeta
{
	public function __construct(
		public readonly string $title,
		public readonly string $description,
		public readonly string $canonical,
		public readonly bool $index,
		public readonly bool $follow,
		public readonly string $ogTitle,
		public readonly string $ogDescription,
		public readonly ?string $ogImage,
		public readonly string $ogType = 'website',
		public readonly string $siteName = '',
	) {
	}
}
