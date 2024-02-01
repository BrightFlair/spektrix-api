<?php
/**
 * This example attempts to find a customer by the email address, supplied as
 * the first argument to the script. If there is no match, it will create a new
 * customer.
 *
 * The script requires the following arguments, in order:
 * 1. email address
 * 2. first name
 * 3. last name
 * 4. mobile number
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
$firstName = $argv[2] ?? null;
if(!$firstName) {
	fwrite(STDERR, "No first name supplied\n");
	exit(1);
}
$lastName = $argv[3] ?? null;
if(!$lastName) {
	fwrite(STDERR, "No last name supplied\n");
	exit(1);
}
$mobileNumber = $argv[4] ?? null;
if(!$mobileNumber) {
	fwrite(STDERR, "No mobile number supplied\n");
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
	echo "Creating new customer...\n";

	$customer = $client->createCustomer($email, $firstName, $lastName, $mobileNumber);
	echo "New customer created successfully:\n";
	echo "ID: $customer->id\n";
	echo "Email: $customer->email\n";
	echo "First name: $customer->firstName\n";
	echo "Last name: $customer->lastName\n";
	echo "Mobile: $customer->mobile\n";
}
