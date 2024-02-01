<?php
namespace BrightFlair\SpektrixAPI;

use Gt\Fetch\Http;
use Gt\Http\Response;
use Gt\Json\JsonObject;
use Gt\Json\JsonPrimitive\JsonArrayPrimitive;
use Gt\Json\JsonPrimitive\JsonNullPrimitive;

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

	public function createCustomer(
		string $email,
		?string $firstName = null,
		?string $lastName = null,
		?string $mobile = null,
	):Customer {
		$endpoint = Endpoint::createCustomer;
		$authenticatedRequest = new AuthenticatedRequest(
			$this->secretKey,
			$endpoint,
			$this->client,
			[
				"email" => $email,
				"firstName" => $firstName ?? "",
				"lastName" => $lastName ?? "",
				"mobile" => $mobile ?? "",
				"birthDate" => ("D, d M Y H:i:s T"),
				"friendlyId" => uniqid(),
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

		// TODO:
		throw new SpektrixAPIException("Something went wrong...");
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

	public function getTag(string $id):Tag {
		foreach($this->getAllTags() as $tag) {
			if($tag->id === $id) {
				return $tag;
			}
		}

		throw new TagNotFoundException($id);
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

	/** @return array<Tag> */
	public function getTagsForCustomer(Customer|string $customer):array {
		$customerId = $customer instanceof Customer
			? $customer->id
			: $customer;

		$endpoint = Endpoint::getCustomerTags;
		$authenticatedRequest = new AuthenticatedRequest(
			$this->secretKey,
			$endpoint,
			$this->client,
			[
				"id" => $customerId,
			]
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

	public function addTagToCustomer(
		Tag|string $tag,
		Customer|string $customer,
	):Tag {
		$tagId = $tag instanceof Tag
			? $tag->id
			: $tag;
		$customerId = $customer instanceof Customer
			? $customer->id
			: $customer;

		$endpoint = Endpoint::addTagToCustomer;
		$authenticatedRequest = new AuthenticatedRequest(
			$this->secretKey,
			$endpoint,
			$this->client,
			[
				"id" => $customerId,
				"tagId" => $tagId,
			]
		);

		if($json = $this->json($authenticatedRequest)) {
			return new Tag(
				$json->getString("id"),
				$json->getString("name"),
			);
		}

		throw new SpektrixAPIException("Error adding tag ID $tagId to customer $customerId");
	}


	public function removeTagFromCustomer(
		Tag|string $tag,
		Customer|string $customer,
	):void {
		$tagId = $tag instanceof Tag
			? $tag->id
			: $tag;
		$customerId = $customer instanceof Customer
			? $customer->id
			: $customer;

		$endpoint = Endpoint::removeTagFromCustomer;
		$authenticatedRequest = new AuthenticatedRequest(
			$this->secretKey,
			$endpoint,
			$this->client,
			[
				"id" => $customerId,
				"tagId" => $tagId,
			]
		);

		$this->json($authenticatedRequest);
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

		if($authenticatedRequest->body) {
			$httpHeaders["Content-type"] = "application/json";
		}

		$init = [
			"method" => $authenticatedRequest->httpMethod,
			"headers" => $httpHeaders,
		];

		if($authenticatedRequest->body) {
			$init["body"] = $authenticatedRequest->body;
		}

		$response = $this->http->awaitFetch($authenticatedRequest->uri, $init);
		if(!$response->ok) {
			if($response->status === 404) {
				return null;
			}

			throw new SpektrixAPIException("HTTP $response->status");
		}

		if($response->status === 204) {
			return new JsonNullPrimitive();
		}

		return $response->awaitJson();
	}

}
