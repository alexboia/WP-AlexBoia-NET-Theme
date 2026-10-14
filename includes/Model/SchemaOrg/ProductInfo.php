<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class ProductInfo {
	public function __construct(
		public string $name,
		public string $description,
		public string $imageUrl,
		public string $price = '',
		public string $currency = '',
		public float $rating = 0.0,
		public int $reviewCount = 0
	){
		return;
	}
}
