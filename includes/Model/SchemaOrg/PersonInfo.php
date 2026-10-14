<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class PersonInfo {
	public function __construct(
		public string $name,
		public string $url = '',
		public string $email = '',
		public string $id = '',
		public string $imageUrl = '',
		public array $sameAs = array()
	){
		return;
	}
}