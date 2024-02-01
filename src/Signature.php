<?php
namespace BrightFlair\SpektrixAPI;

class Signature {
	const AUTH_PREFIX = "SpektrixAPI3";
	public readonly string $date;
	public readonly string $signedString;

	public function __construct(
		string $secretKeyBase64,
		string $httpMethod,
		string $uri,
		?string $bodyString = null,
	) {
		$this->date = gmdate("D, d M Y H:i:s T");
		$stringToSign = $httpMethod
			. "\n" . $uri
			. "\n" . $this->date;
		if($httpMethod !== "GET") {
			$md5BodyString = md5($bodyString ?? "", true);
			$base64EncodedBodyString = base64_encode($md5BodyString);
			$stringToSign .= "\n$base64EncodedBodyString";
		}

		$decodedSecretKey = base64_decode($secretKeyBase64);
		$utf8encodedStringToSign = mb_convert_encoding(
			$stringToSign,
			"UTF-8",
			"ISO-8859-1",
		);
		$this->signedString = hash_hmac(
			"sha1",
			$utf8encodedStringToSign,
			$decodedSecretKey,
			true,
		);
	}
}
