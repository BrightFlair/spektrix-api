<?php
/**
 * This example lists out all tags on the account.
 *
 * For any example to work, you must supply your own secrets in config.ini:
 * - username
 * - client
 * - secret_key
 */

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$config = parse_ini_file("config.ini");
$client = new BrightFlair\SpektrixAPI\Client(
	$config["username"],
	$config["client"],
	$config["secret_key"],
);

foreach($client->getAllTags() as $tag) {

}
