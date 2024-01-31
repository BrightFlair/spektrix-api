<?php
namespace BrightFlair\SpektrixAPI;

class AuthenticatedRequest {
	public string $httpMethod;
	public readonly string $uri;
	public readonly Signature $signature;

	/** @param array<string, string> $kvp */
	public function __construct(
		string $secretKey,
		Endpoint $endpoint,
		string $client,
		array $kvp = [],
	) {
		[$httpMethod, $endpointPath] = explode(
			" ",
			$endpoint->value,
			2,
		);
		$bodyString = null;
		if(str_contains($endpointPath, " ")) {
			[$endpointPath, $bodyString] = explode(
				" ",
				$endpointPath,
			);
		}

		$uri = implode("/", [
			Client::BASE_URI,
			$endpointPath,
		]);
		$uri = str_replace("{client}", $client, $uri);
		foreach($kvp as $key => $value) {
			$uri = str_replace(
				"{" . $key . "}",
				$value,
				$uri,
			);
		}

		$this->httpMethod = $httpMethod;
		$this->uri = $uri;
		$this->signature = new Signature(
			$secretKey,
			$httpMethod,
			$this->uri,
			$bodyString,
		);
	}
}
