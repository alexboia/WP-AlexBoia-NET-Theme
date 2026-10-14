<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class WebSiteInfo {
	public function __construct(
		public string $name,
		public string $url,
		public string $description = '',
		public string $alternateName = '',
		public string $id = '',
		public string $publisherId = '',
		public string $language = ''
	){
		return;
	}
}
