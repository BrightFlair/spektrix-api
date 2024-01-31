<?php
namespace BrightFlair\SpektrixAPI;

class Tag {
	public function __construct(
		public readonly string $id,
		public readonly string $name,
	) {}
}
