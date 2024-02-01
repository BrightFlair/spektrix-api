<?php
namespace BrightFlair\SpektrixAPI\Test;

use BrightFlair\SpektrixAPI\Signature;
use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase {
	public function testSignedString():void {
		$secretKey = base64_encode("this is a test");
		$httpMethod = "GET";
		$uri = "v0/test";
		$sut = new Signature(
			$secretKey,
			$httpMethod,
			$uri,
		);

// Manually follow this guide: https://integrate.spektrix.com/docs/authentication#constructing-the-authorization-header
		$date = gmdate("D, d M Y H:i:s T");
		$stringToSign = "$httpMethod\n$uri\n$date";
		$utf8encodedStringToSign = mb_convert_encoding(
			$stringToSign,
			"UTF-8",
			"ISO-8859-1",
		);
		$expectedSignedString = hash_hmac(
			"sha1",
			$utf8encodedStringToSign,
			base64_decode($secretKey),
			true,
		);
		self::assertSame($expectedSignedString, $sut->signedString);
	}

	public function testSignedStringPost():void {
		$secretKey = base64_encode("this is a test");
		$httpMethod = "POST";
		$uri = "v0/test";
		$bodyString = json_encode(["name" => "test"]);
		$sut = new Signature(
			$secretKey,
			$httpMethod,
			$uri,
			$bodyString,
		);

// Manually follow this guide: https://integrate.spektrix.com/docs/authentication#constructing-the-authorization-header
		$date = gmdate("D, d M Y H:i:s T");
		$stringToSign = "$httpMethod\n$uri\n$date\n" . base64_encode(md5($bodyString, true));
		$utf8encodedStringToSign = mb_convert_encoding(
			$stringToSign,
			"UTF-8",
			"ISO-8859-1",
		);
		$expectedSignedString = hash_hmac(
			"sha1",
			$utf8encodedStringToSign,
			base64_decode($secretKey),
			true,
		);
		self::assertSame($expectedSignedString, $sut->signedString);
	}
}
