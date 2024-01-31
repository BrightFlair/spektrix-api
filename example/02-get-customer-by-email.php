<?php
/**
 * This example finds a customer by the email address, supplied as the first
 * argument to the script.
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

try {
	$customer = $client->getCustomer(email: $email);
	echo "Customer found!\n";
	echo "ID: $customer->id\n";
	echo "Email: $customer->email\n";
	echo "First name: $customer->firstName\n";
	echo "Last name: $customer->lastName\n";
}
catch(CustomerNotFoundException) {
	echo "No customer was found with the email address $email\n";
}
