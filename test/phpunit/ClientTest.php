<?php
namespace BrightFlair\SpektrixAPI\Test;

use BrightFlair\SpektrixAPI\Client;
use BrightFlair\SpektrixAPI\CustomerNotFoundException;
use BrightFlair\SpektrixAPI\Endpoint;
use BrightFlair\SpektrixAPI\SpektrixAPIException;
use BrightFlair\SpektrixAPI\TagNotFoundException;
use Gt\Fetch\Http;
use Gt\Http\Response;
use Gt\Json\JsonObject;
use Gt\Json\JsonObjectBuilder;
use Gt\Json\JsonPrimitive\JsonArrayPrimitive;
use Gt\Json\JsonPrimitive\JsonNullPrimitive;
use Gt\Promise\Promise;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase {
	const TEST_USERNAME = "test-user";
	const TEST_CLIENT = "test-client";
	const TEST_SECRET_KEY = "super-secret-key";

	public function testGetCustomer_byId():void {
		$testCustomerId = "test-id-123";
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getCustomerById->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{id}", $testCustomerId, $expectedUri);

		$json = self::createMock(JsonObject::class);
		$json->method("getString")->willReturnMap([
			["id", "customer-id"],
			["email", "customer@example.com"],
			["firstName", "Test"],
			["lastName", "Tester"],
			["mobile", "07123456789"],
		]);

		$response = self::createMock(Response::class);
		$response->method("__get")->willReturnMap([
			["ok", true],
			["status", 200],
		]);
		$response->method("getStatusCode")->willReturn(200);
		$response->expects(self::once())
			->method("awaitJson")
			->willReturn($json);

		$fetchClient = self::createMock(Http::class);
		$fetchClient->expects(self::once())
			->method("awaitFetch")
			->with($expectedUri)
			->willReturn($response);

		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$customer = $sut->getCustomer(id: $testCustomerId);
		self::assertSame("customer-id", $customer->id);
		self::assertSame("customer@example.com", $customer->email);
		self::assertSame("07123456789", $customer->mobile);
	}

	public function testCreateCustomer():void {
		$newCustomerId = uniqid("I-");
		$newCustomerEmail = "test@example.com";
		$newCustomerFirstName = "Test";
		$newCustomerLastName = "Tester";
		$newCustomerMobile = "07123456789";

		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::createCustomer->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 200, [
			"id" => $newCustomerId,
			"email" => $newCustomerEmail,
			"firstName" => $newCustomerFirstName,
			"lastName" => $newCustomerLastName,
			"mobile" => $newCustomerMobile,
		]);
		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$customer = $sut->createCustomer($newCustomerEmail, $newCustomerFirstName, $newCustomerLastName, $newCustomerMobile);

		self::assertSame($newCustomerId, $customer->id);
		self::assertSame($newCustomerEmail, $customer->email);
		self::assertSame($newCustomerFirstName, $customer->firstName);
		self::assertSame($newCustomerLastName, $customer->lastName);
		self::assertSame($newCustomerMobile, $customer->mobile);
	}

	public function testGetCustomerById():void {
		$customerId = uniqid("I-");
		$customerEmail = "test@example.com";
		$customerFirstName = "Test";
		$customerLastName = "Tester";
		$customerMobile = "07123456789";

		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getCustomerById->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{id}", $customerId, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 200, [
			"id" => $customerId,
			"email" => $customerEmail,
			"firstName" => $customerFirstName,
			"lastName" => $customerLastName,
			"mobile" => $customerMobile,
		]);
		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$customer = $sut->getCustomer(id: $customerId);
		self::assertSame($customerId, $customer->id);
		self::assertSame($customerEmail, $customer->email);
		self::assertSame($customerFirstName, $customer->firstName);
		self::assertSame($customerLastName, $customer->lastName);
		self::assertSame($customerMobile, $customer->mobile);
	}

	public function testGetCustomerById_notFound():void {
		$customerId = uniqid("I-");

		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getCustomerById->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{id}", $customerId, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 404);
		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		self::expectException(CustomerNotFoundException::class);
		$sut->getCustomer(id: $customerId);
	}

	public function testGetCustomerByEmail():void {
		$customerId = uniqid("I-");
		$customerEmail = "test@example.com";
		$customerFirstName = "Test";
		$customerLastName = "Tester";
		$customerMobile = "07123456789";

		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getCustomerByEmail->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{email}", $customerEmail, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 200, [
			"id" => $customerId,
			"email" => $customerEmail,
			"firstName" => $customerFirstName,
			"lastName" => $customerLastName,
			"mobile" => $customerMobile,
		]);
		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$customer = $sut->getCustomer(email: $customerEmail);
		self::assertSame($customerId, $customer->id);
		self::assertSame($customerEmail, $customer->email);
		self::assertSame($customerFirstName, $customer->firstName);
		self::assertSame($customerLastName, $customer->lastName);
		self::assertSame($customerMobile, $customer->mobile);
	}

	public function testGetCustomerByEmail_notFound():void {
		$customerEmail = "test@example.com";

		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getCustomerByEmail->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{email}", $customerEmail, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 404);
		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		self::expectException(CustomerNotFoundException::class);
		$sut->getCustomer(email: $customerEmail);
	}

	public function testGetAllTags():void {
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getAllTags->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 200, [
			["id" => "id-1", "name" => "name-1"],
			["id" => "id-2", "name" => "name-2"],
			["id" => "id-3", "name" => "name-3"],
		]);

		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$allTags = $sut->getAllTags();

		self::assertCount(3, $allTags);
		foreach($allTags as $i => $tag) {
			$num = $i + 1;
			self::assertSame("id-$num", $tag->id);
			self::assertSame("name-$num", $tag->name);
		}
	}

	public function testGetTagById():void {
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getAllTags->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 200, [
			["id" => "id-1", "name" => "name-1"],
			["id" => "id-2", "name" => "name-2"],
			["id" => "id-3", "name" => "name-3"],
		]);

		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$tag = $sut->getTag(id: "id-2");

		self::assertSame("name-2", $tag->name);
	}

	public function testGetTagById_notFound():void {
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getAllTags->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 200, [
			["id" => "id-1", "name" => "name-1"],
			["id" => "id-2", "name" => "name-2"],
			["id" => "id-3", "name" => "name-3"],
		]);

		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		self::expectException(TagNotFoundException::class);
		$sut->getTag(id: "id-4");
	}

	public function testGetTagsForCustomer():void {
		$customerId = uniqid("I-");
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getCustomerTags->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{id}", $customerId, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 200, [

				["id" => "id-1", "name" => "name-1"],
				["id" => "id-2", "name" => "name-2"],
		]);

		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$allTags = $sut->getTagsForCustomer($customerId);

		self::assertCount(2, $allTags);
		foreach($allTags as $i => $tag) {
			$num = $i + 1;
			self::assertSame("id-$num", $tag->id);
			self::assertSame("name-$num", $tag->name);
		}
	}

	public function testAddTagToCustomer():void {
		$customerId = uniqid("I-");
		$tagId = "tag-id-1";
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::addTagToCustomer->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{id}", $customerId, $expectedUri);
		$expectedUri = str_replace("{tagId}", $tagId, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 200, [
			"id" => $tagId,
			"name" => "Example tag name",
		]);

		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$tag = $sut->addTagToCustomer($tagId, $customerId);
		self::assertSame($tagId, $tag->id);
		self::assertSame("Example tag name", $tag->name);
	}

	public function testAddTagToCustomer_invalid():void {
		$customerId = uniqid("I-");
		$tagId = "tag-id-1-does-not-exist";
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::addTagToCustomer->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{id}", $customerId, $expectedUri);
		$expectedUri = str_replace("{tagId}", $tagId, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 404);

		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		self::expectException(SpektrixAPIException::class);
		self::expectExceptionMessage("Error adding tag ID $tagId to customer $customerId");
		$sut->addTagToCustomer($tagId, $customerId);
	}

	public function testRemoveTagFromCustomer():void {
		$customerId = uniqid("I-");
		$tagId = "tag-id-1";
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::removeTagFromCustomer->value)[1];
		$expectedUri = str_replace("{client}", self::TEST_CLIENT, $expectedUri);
		$expectedUri = str_replace("{id}", $customerId, $expectedUri);
		$expectedUri = str_replace("{tagId}", $tagId, $expectedUri);

		$fetchClient = self::getFetchClient($expectedUri, 204);

		$sut = new Client(self::TEST_USERNAME, self::TEST_CLIENT, self::TEST_SECRET_KEY, $fetchClient);
		$sut->removeTagFromCustomer($tagId, $customerId);
	}

	private function getFetchClient(
		string $uri,
		int $status,
		?array $responseData = null,
	):Http {
		$builder = new JsonObjectBuilder();
		$json = is_null($responseData)
			? new JsonNullPrimitive()
			: $builder->fromJsonString(json_encode($responseData));
		$response = self::createMock(Response::class);
		$response->expects(self::exactly(is_null($responseData) ? 0 : 1))
			->method("awaitJson")
			->willReturn($json);

		$response->method("__get")->willReturnMap([
			["ok", $status >= 200 && $status < 300],
			["status", $status],
		]);

		$http = self::createMock(Http::class);
		$http->expects(self::once())
			->method("awaitFetch")
			->with($uri)
			->willReturn($response);

		return $http;
	}
}
