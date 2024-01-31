<?php
namespace BrightFlair\SpektrixAPI\Test;

use BrightFlair\SpektrixAPI\Client;
use BrightFlair\SpektrixAPI\Endpoint;
use Gt\Fetch\Http;
use Gt\Http\Response;
use Gt\Json\JsonObject;
use Gt\Json\JsonPrimitive\JsonArrayPrimitive;
use Gt\Promise\Promise;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase {
	public function testGetCustomer_byId():void {
		$username = "test-user";
		$client = "test-client";
		$secretKey = "super-secret-key";

		$testCustomerId = "test-id-123";
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getCustomerById->value)[1];
		$expectedUri = str_replace("{client}", $client, $expectedUri);
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

		$sut = new Client($username, $client, $secretKey, $fetchClient);
		$customer = $sut->getCustomer(id: $testCustomerId);
		self::assertSame("customer-id", $customer->id);
		self::assertSame("customer@example.com", $customer->email);
		self::assertSame("07123456789", $customer->mobile);
	}

	public function testGetAllTags():void {
		$username = "test-user";
		$client = "test-client";
		$secretKey = "super-secret-key";

		$testCustomerId = "test-id-123";
		$expectedUri = Client::BASE_URI . "/";
		$expectedUri .= explode(" ", Endpoint::getAllTags->value)[1];
		$expectedUri = str_replace("{client}", $client, $expectedUri);
		$expectedUri = str_replace("{id}", $testCustomerId, $expectedUri);

		$jsonTag1 = self::createMock(JsonObject::class);
		$jsonTag1->method("getString")->willReturnMap([
			["id", "id-1"],
			["name", "name-1"],
		]);
		$jsonTag2 = self::createMock(JsonObject::class);
		$jsonTag2->method("getString")->willReturnMap([
			["id", "id-2"],
			["name", "name-2"],
		]);
		$jsonTag3 = self::createMock(JsonObject::class);
		$jsonTag3->method("getString")->willReturnMap([
			["id", "id-3"],
			["name", "name-3"],
		]);

		$json = self::createMock(JsonArrayPrimitive::class);
		$json->method("getPrimitiveValue")->willReturn([
			$jsonTag1,
			$jsonTag2,
			$jsonTag3,
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

		$sut = new Client($username, $client, $secretKey, $fetchClient);
		$allTags = $sut->getAllTags();

		self::assertCount(3, $allTags);
		foreach($allTags as $i => $tag) {
			$num = $i + 1;
			self::assertSame("id-$num", $tag->id);
			self::assertSame("name-$num", $tag->name);
		}
	}
}
