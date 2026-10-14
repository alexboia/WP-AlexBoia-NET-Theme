<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class ProfilePageInfo {
	public function __construct(
		public string $name,
		public string $url,
		public string $mainEntityId,
		public string $dateCreated = '',
		public string $dateModified = '',
		public string $id = ''
	){
		return;
	}
}
