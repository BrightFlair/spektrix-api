<?php
namespace BrightFlair\SpektrixAPI;

enum Endpoint:string {
	case getCustomerById = "GET v3/customers/{id}";
	case getCustomerByEmail = "GET v3/customers?email={email}";
	case getAllTags = "GET v3/tags";
	case getCustomerTags = "GET v3/customers/{id}/tags";
	case addTagToCustomer = "POST v3/customers/{id}/tags id={tagId}";
	case removeTagFromCustomer = "DELETE v3/customers/tags/{tagId}?id={id}";
}
