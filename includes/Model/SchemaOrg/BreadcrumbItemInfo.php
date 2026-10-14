<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class BreadcrumbItemInfo {
	public function __construct(
		public string $name,
		public string $url = ''
	){
		return;
	}
}
