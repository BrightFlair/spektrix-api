<?php
namespace BrightFlair\SpektrixAPI;

class AuthenticatedRequest {
	public string $httpMethod;
	public readonly string $uri;
	public readonly Signature $signature;
	public readonly ?string $body;

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
		$httpBodyString = null;
		if(str_contains($endpointPath, " ")) {
			[$endpointPath, $httpBodyString] = explode(
				" ",
				$endpointPath,
			);
		}

		$uri = implode("/", [
			Client::BASE_URI,
			$endpointPath,
		]);
		$uri = str_replace("{client}", $client, $uri);
		/**
		 * @var string $key
		 * @var ?string $value
		 */
		foreach($kvp as $key => $value) {
			$uri = str_replace(
				"{" . $key . "}",
				$value ?? "",
				$uri,
			);

			if($httpBodyString) {
				$httpBodyString = str_replace(
					"{" . $key . "}",
					$value,
					$httpBodyString,
				);
			}
		}

		$this->httpMethod = $httpMethod;
		$this->uri = $uri;

		$jsonBodyString = null;
		if($httpBodyString) {
			parse_str($httpBodyString, $bodyKvp);
			$jsonBodyString = json_encode($bodyKvp);
		}

		$this->body = $jsonBodyString;
		$this->signature = new Signature(
			$secretKey,
			$httpMethod,
			$this->uri,
			$this->body,
		);
	}
}
