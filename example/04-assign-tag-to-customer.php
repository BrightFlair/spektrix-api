<?php
/**
 * This example assigns an existing tag to an existing customer. The tag ID must
 * be the first argument to the script, the customer ID must be the second
 * argument to the script.
 *
 * For any example to work, you must supply your own secrets in config.ini:
 * - username
 * - client
 * - secret_key
 */

use BrightFlair\SpektrixAPI\CustomerNotFoundException;
use BrightFlair\SpektrixAPI\TagNotFoundException;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$config = parse_ini_file("config.ini");
$client = new BrightFlair\SpektrixAPI\Client(
	$config["username"],
	$config["client"],
	$config["secret_key"],
);

$tagId = $argv[1] ?? null;
if(!$tagId) {
	fwrite(STDERR, "No tag ID supplied\n");
	exit(1);
}

$customerId = $argv[2] ?? null;
if(!$customerId) {
	fwrite(STDERR, "No customer ID supplied\n");
	exit(2);
}

$tag = null;
try {
	$tag = $client->getTag(id: $tagId);
}
catch(TagNotFoundException) {
	fwrite(STDERR, "No tag found with ID $tagId\n");
	exit(3);
}

$customer = null;
try {
	$customer = $client->getCustomer(id: $customerId);
}
catch(CustomerNotFoundException) {
	fwrite(STDERR, "No customer found with ID $customerId\n");
	exit(4);
}

$client->addTagToCustomer($tag, $customer);
