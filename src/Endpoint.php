<?php
namespace BrightFlair\SpektrixAPI;

enum Endpoint:string {
	case createCustomer = "POST v3/customers email={email}&firstName={firstName}&lastName={lastName}&mobile={mobile}"; //&Phone={mobile}&Title=XX&Password=ABCDEFGHIJKLMNOPQRSTUVWXYZ&BirthDate=2024-01-31T22:45:56.2997626+00:00
	case getCustomerById = "GET v3/customers/{id}";
	case getCustomerByEmail = "GET v3/customers?email={email}";
	case getAllTags = "GET v3/tags";
	case getCustomerTags = "GET v3/customers/{id}/tags";
	case addTagToCustomer = "POST v3/customers/{id}/tags id={tagId}";
	case removeTagFromCustomer = "DELETE v3/customers/tags/{tagId}?id={id}";
}
