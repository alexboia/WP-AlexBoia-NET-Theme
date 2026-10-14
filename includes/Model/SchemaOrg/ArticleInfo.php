<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class ArticleInfo {
	public function __construct(
		public string $title,
		public string $content,
		public string $imageUrl,
		public string $authorName,
		public string $datePublished,
		public string $dateModified = '',
		public string $id = '',
		public string $publisherId = ''
	){
		return;
	}
}
