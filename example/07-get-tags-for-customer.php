<?php
/**
 * This example lists out all tags for one customer.
 *
 * For any example to work, you must supply your own secrets in config.ini:
 * - username
 * - client
 * - secret_key
 */

use BrightFlair\SpektrixAPI\CustomerNotFoundException;

chdir(dirname(__DIR__));
require "vendor/autoload.php";

$config = parse_ini_file("config.ini");
$client = new BrightFlair\SpektrixAPI\Client(
	$config["username"],
	$config["client"],
	$config["secret_key"],
);

$email = $argv[1] ?? null;
if(!$email) {
	fwrite(STDERR, "No email address supplied\n");
	exit(1);
}
$tagList =[];

try {
	$customer = $client->getCustomer(email: $email);
	echo "Customer found!\n";
	echo "ID: $customer->id\n";
	echo "Email: $customer->email\n";
	echo "First name: $customer->firstName\n";
	echo "Last name: $customer->lastName\n";
	$tagList = $client->getTagsForCustomer($customer);

}
catch(CustomerNotFoundException) {
	echo "No customer was found with the email address $email\n";
}
echo "There are " . count($tagList) . " tags on the account:\n";

foreach($tagList as $i => $tag) {
	echo $i + 1;
	echo ": ";
	echo $tag->name;
	echo " ($tag->id)";
	echo "\n";
}
