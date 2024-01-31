<?php
namespace BrightFlair\SpektrixAPI;

class Customer {
	public function __construct(
		public string $id,
		public string $email,
		public ?string $firstName = null,
		public ?string $lastName = null,
		public ?string $mobile = null,
	) {}
}
