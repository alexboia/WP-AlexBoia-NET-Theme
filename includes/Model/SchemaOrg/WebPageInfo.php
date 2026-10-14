<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class WebPageInfo {
	public function __construct(
		public string $name,
		public string $description = '',
		public string $url = ''
	){
		return;
	}
}
