<?php
namespace BrightFlair\SpektrixAPI;

use Gt\Fetch\Http;
use Gt\Http\Response;
use Gt\Json\JsonObject;
use Gt\Json\JsonPrimitive\JsonArrayPrimitive;

readonly class Client {
	const USER_AGENT = "github.com/BrightFlair/SpektrixAPI";
	const BASE_URI = "https://system.spektrix.com/{client}/api";

	private Http $http;

	public function __construct(
		private string $username,
		private string $client,
		private string $secretKey,
		Http $fetchClient = null,
	) {
		$this->http = $fetchClient ?? new Http();
	}

	public function getCustomer(
		?string $id = null,
		?string $email = null,
	):Customer {
		$endpoint = is_null($email)
			? Endpoint::getCustomerById
			: Endpoint::getCustomerByEmail;
		$authenticatedRequest = new AuthenticatedRequest(
			$this->secretKey,
			$endpoint,
			$this->client,
			[
				"id" => $id,
				"email" => $email,
			]
		);

		if($json = $this->json($authenticatedRequest)) {
			return new Customer(
				$json->getString("id"),
				$json->getString("email"),
				firstName: $json->getString("firstName"),
				lastName: $json->getString("lastName"),
				mobile: $json->getString("mobile"),
			);
		}

		throw new CustomerNotFoundException($email ?? $id);
	}

	/** @return array<Tag> */
	public function getAllTags():array {
		$endpoint = Endpoint::getAllTags;
		$authenticatedRequest = new AuthenticatedRequest(
			$this->secretKey,
			$endpoint,
			$this->client
		);

		$tagList = [];
		/** @var JsonArrayPrimitive $jsonArray */
		$jsonArray = $this->json($authenticatedRequest);
		foreach($jsonArray->getPrimitiveValue() as $item) {
			array_push(
				$tagList,
				new Tag(
					$item->getString("id"),
					$item->getString("name"),
				)
			);
		}

		return $tagList;
	}

	private function json(AuthenticatedRequest $authenticatedRequest):?JsonObject {
		$authorizationHeader = Signature::AUTH_PREFIX
			. " "
			. $this->username
			. ":"
			. base64_encode($authenticatedRequest->signature->signedString);

		$httpHeaders = [
			"Accept" => "application/json",
			"User-agent" => Client::USER_AGENT,
			"Date" => $authenticatedRequest->signature->date,
			"Host" => parse_url($authenticatedRequest->uri, PHP_URL_HOST),
			"Authorization" => $authorizationHeader,
		];

		$response = $this->http->awaitFetch($authenticatedRequest->uri, [
			"method" => $authenticatedRequest->httpMethod,
			"headers" => $httpHeaders,
		]);
		if(!$response->ok) {
			if($response->status === 404) {
				return null;
			}

			throw new SpektrixAPIException("HTTP $response->status");
		}

		return $response->awaitJson();
	}

}
