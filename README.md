Self-documenting, type-safe Spektrix web API.
=============================================

This is an unofficial library for accessing [Spektrix's v3 API](https://integrate.spektrix.com/docs/API3), built to abstract complex authentication in a self-documenting, type-safe PHP API.

Getting an access key
---------------------

Official docs here: https://support.spektrix.com/hc/en-us/articles/360013592837-Setting-Up-an-API-User

1. Associate a username, email address and mobile telephone number to a new API user.
2. The **username** is required, along with a provided **client name**, to access the API.
3. Email address will receive a link to click with a one-off token.
4. On the page linked via email, enter a code sent via SMS.
5. You will receive your **secret API key** - this is used when construct the `Client` object.

Authentication
--------------

API authentication is abstracted by this library, because it involves a multiple-step time-sensitive SHA-1 hashing exercise. To authenticate this library, pass your **username** and **client name** into the constructor of the `Client` object.

```php
$username = "greg\brightflair";
$clientName = "api-test";
$secretKey = "S2VlcCB5b3VyIGtuaWNrZXJzIG9uISBUaGlzIGlzIGEgdGVzdCBBUEkga2V5LCBkb24ndCB3b3JyeSE=";
$client = new BrightFlair\SpektrixAPI\Client($username, $clientName, $secretKey);
```

Endpoints of the API
--------------------

Endpoints are mapped to type-safe functions in the library. Use your IDE for self-documentation, with the following naming convention:

- Functions begin with "create" / "get" / "update".
- Functions then include the entity name, such as "customer" / "tag".
- Matching mechanisms are provided as named parameters, such as "id" / "name".

Example call to get a customer, get a tag by its name, and add the tag to the customer's record:

```php
$customer = $client->getCustomerByEmail("greg.bowler@brightflair.com");
$tag = $client->getTagByName("Example tag");
$client->addTagToCustomer($customer, $tag);
```

Missing functionality
---------------------

I have built this library for my own use to integrate with [Nimbus Disability](https://www.nimbusdisability.com). I don't plan on building any more functionality than is required for the type of integration I'm using, [unless someone persuades me](https://github.com/sponsors/g105b) to develop it further.
