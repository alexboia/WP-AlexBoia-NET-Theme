<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class OrganizationInfo {
	public function __construct(
		public string $name,
		public string $description = '',
		public string $url = '',
		public string $logoUrl = '',
		public string $id = ''
	){
		return;
	}
}