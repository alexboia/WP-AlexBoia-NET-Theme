<?php
namespace AbNetTheme\Model\SchemaOrg;

final readonly class RecipeInfo {
	public function __construct(
		public string $name,
		public string $description,
		public string $imageUrl,
		public string $prepTime,
		public string $cookTime,
		public string $totalTime,
		public int $yield,
		public array $ingredients = array(),
		public array $instructions = array()
	){
		return;
	}
}
