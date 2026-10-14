<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class LocalBusinessInfo {
	public function __construct(
		public string $name,
		public string $type,
		public string $url,
		public string $phone = '',
		public array $address = array()
	){
		return;
	}
}
