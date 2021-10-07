# idealo Orders-API v2: PHP SDK
# Implementation Guide

## License and usage
This SDK can be used under the conditions of the Apache License 2.0, see LICENSE for details

## Technical requirements
- Standard Apache webserver with at least PHP 7.4
- The curl library for PHP

## Introduction

This is not an official library update for the Orders API v2 from Idealo, but from Uhrenlounge Dresden!

The implementation of the idealo SDK is  easy and straightforward.
Please test and integrate this SDK only with Idealo's sandbox environment. Generate an appropriate key on the Idealo Business page.
You have any question? Than write me a mail. tom.gottschlich@uhrenlounge.de

## Basics

The SDK has an autoloader file, which automatically loads the class(es) of the SDK, so that you can use all of them in your project.
Simply include the autoloader file at the spot in your code, where you create the instance of the client object using "require_once".

	require_once dirname(__FILE__).'/sdk/autoload.php';

Then you can instantiate the REST-client-class from anywhere in your code like this:

	$oClient = new idealo\Direktkauf\Client();

The client needs 2 parameters:
1. client - The client key for the authentification
2. secret - The secret key for the authentification (You find this value only when you generate a new API key)
3. isLive - true for live-mode and false for test-mode (Is optional. Default is false)

You can either put them right in the constructor:

	$client = '4i23uh4i2-iom4o324-m42m-opm32km4';
    $secret = 'nfn78sdfn!osadf?32';
	$isLiveMode = true;
	$oClient = new idealo\Direktkauf\Client($client, $secret, $isLiveMode);

## Implementation

With this client object you have direct access to all the v2 REST-API functions from idealo.

At the moment there the following 10 requests available (5 more than in the v1 REST-API from idealo): 

### `$oClient->getOrders(): array`

Requests all orders from idealo.
They are delivered as an associative array, directly like idealo delivers them in the following format:

    {
        "content": [
            {
              "idealoOrderId": "008HPCL48R",
              "created": "2021-10-05T12:54:46Z",
              "updated": "2021-10-05T12:54:46.336Z",
              "status": "PROCESSING",
              "currency": "EUR",
              "offersPrice": "16180.00",
              "grossPrice": "16180.00",
              "shippingCosts": "0.00",
              "lineItems": [
                {
                  "title": "Certina Heritage DS Caimano Gent C017.410.11.057.00",
                  "price": "280.00",
                  "priceRangeAmount": "5.60",
                  "quantity": 1,
                  "sku": "1295",
                  "merchantDeliveryText": "1-2+Werktage"
                },
                {
                  "title": "Baume & Mercier Riviera Gent 42mm M0A10620",
                  "price": "2650.00",
                  "quantity": 6,
                  "sku": "28465",
                  "merchantDeliveryText": "1-2+Werktage"
                }
              ],
              "customer": {
                "email": "m-jk0vb80vm4h87u66@checkout-stg.idealo.de"
              },
              "payment": {
                "paymentMethod": "PAYPAL",
                "transactionId": "snakeoil-0a0e054"
              },
              "billingAddress": {
                "salutation": "MR",
                "firstName": "Sabine",
                "lastName": "Fischer",
                "addressLine1": "Straße 82",
                "postalCode": "48381",
                "city": "Ort",
                "countryCode": "DE"
              },
              "shippingAddress": {
                "salutation": "MR",
                "firstName": "Sabine",
                "lastName": "Fischer",
                "addressLine1": "Straße 82",
                "postalCode": "48381",
                "city": "Ort",
                "countryCode": "DE"
              },
              "fulfillment": {
                "method": "POSTAL",
                "tracking": [],
                "options": []
              },
              "refunds": []
            },
            ...
        ],
        "totalElements": 36,
        "totalPages": 1
    }

### `$oClient->getOrder(string $idealoOrderId): array`

Get a specific order by idealoOrderId:

	{
        "idealoOrderId": "008HPCL48R",
        "created": "2021-10-05T12:54:46Z",
        "updated": "2021-10-05T12:54:46.336Z",
        "status": "PROCESSING",
        "currency": "EUR",
        "offersPrice": "16180.00",
        "grossPrice": "16180.00",
        "shippingCosts": "0.00",
        "lineItems": [
            {
                "title": "Certina Heritage DS Caimano Gent C017.410.11.057.00",
                "price": "280.00",
                "priceRangeAmount": "5.60",
                "quantity": 1,
                "sku": "1295",
                "merchantDeliveryText": "1-2+Werktage"
            },
            {
                "title": "Baume & Mercier Riviera Gent 42mm M0A10620",
                "price": "2650.00",
                "quantity": 6,
                "sku": "28465",
                "merchantDeliveryText": "1-2+Werktage"
            }
        ],
        "customer": {
            "email": "m-jk0vb80vm4h87u66@checkout-stg.idealo.de"
        },
        "payment": {
            "paymentMethod": "PAYPAL",
            "transactionId": "snakeoil-0a0e054"
        },
        "billingAddress": {
            "salutation": "MR",
            "firstName": "Sabine",
            "lastName": "Fischer",
            "addressLine1": "Straße 82",
            "postalCode": "48381",
            "city": "Ort",
            "countryCode": "DE"
        },
        "shippingAddress": {
            "salutation": "MR",
            "firstName": "Sabine",
            "lastName": "Fischer",
            "addressLine1": "Straße 82",
            "postalCode": "48381",
            "city": "Ort",
            "countryCode": "DE"
        },
        "fulfillment": {
            "method": "POSTAL",
            "tracking": [],
            "options": []
        },
        "refunds": []
    }

### `$oClient->getNewOrder(): array`

    {
        "idealoOrderId" : "A1B2C3D4",
        "merchantOrderNumber" : "1234ABC",
        "created" : "2021-01-01T00:00:00Z",
        "updated" : "2021-01-01T00:00:00Z",
        "status" : "PROCESSING",
        "currency" : "EUR",
        "offersPrice" : "50.85",
        "grossPrice" : "53.84",
        "shippingCosts" : "2.99",
        "lineItems" : [
            {
                "title" : "Example product 1",
                "price" : "30.55",
                "priceRangeAmount" : "1.44",
                "quantity" : 1,
                "sku" : "product-sku-12345",
                "merchantId" : "merchant_12345",
                "merchantName" : "Example Electronics Ltd",
                "merchantDeliveryText" : "Delivered within 3 working days"
            }, 
            {
                "title" : "Example product 2",
                "price" : "10.15",
                "quantity" : 2,
                "sku" : "product-sku-5648",
                "merchantId" : "merchant_12345",
                "merchantName" : "Example Electronics Ltd",
                "merchantDeliveryText" : "Delivered within 3 working days"
            } 
        ],
        "customer" : {
            "email" : "m-zvvtu596gbz00t0@checkout.idealo.de",
            "phone" : "030-1231234"
        },
        "payment" : {
            "paymentMethod" : "IDEALO_CHECKOUT_PAYMENTS",
            "transactionId" : "acb-123"
        },
        "billingAddress" : {
            "salutation" : "MR",
            "firstName" : "Max",
            "lastName" : "Mustermann",
            "addressLine1" : "Ritterstraße 11",
            "addressLine2" : "c/o idealo",
            "postalCode" : "10969",
            "city" : "Berlin",
            "countryCode" : "DE"
        },
        "shippingAddress" : {
            "salutation" : "MR",
            "firstName" : "Max",
            "lastName" : "Mustermann",
            "addressLine1" : "Ritterstraße 11",
            "addressLine2" : "c/o idealo",
            "postalCode" : "10969",
            "city" : "Berlin",
            "countryCode" : "DE"
        },
        "fulfillment" : {
            "method" : "FORWARDING",
            "tracking" : [ {
                "code" : "xyz1234",
                "carrier" : "Cargo"
            } ],
            "options" : [ {
                "forwardOption" : "TWO_MAN_DELIVERY",
                "price" : "2.99"
            } ]
        },
        "refunds" : [ {
            "refundId" : "example-refund-id",
            "refundTransactionId" : "example-refund-transaction-id",
            "status" : "OPEN",
            "currency" : "EUR",
            "refundAmount" : 1.99,
            "created" : "2021-09-08T09:43:33.433517Z",
            "updated" : "2021-09-08T09:43:33.433518Z"
        } ],
        "voucher" : {
            "code" : "FXWFGE (30%, max. 5 EUR)"
        }
    } 

#### Parameters

`sIdealoOrderNr` - The order-nr you got from idealo in the "order_number" from the getOrders request
`sShopOrderNr` - The order-nr this idealo order received in your shop.

This request transmits and connects the order-number from your shop-system to the idealo-order.

### `sendFulfillmentStatus($sIdealoOrderNr, $sTrackingCode, $sCarrier)`

#### Parameters

`sIdealoOrderNr` - The order-nr you got from idealo in the "order_number" from the getOrders request
`sTrackingCode` (optional) - The trackingcode for the current order
`sCarrier` (optional) - The shipping-carrier for the current order ( DHL, DPD, UPS, FedEx, ...)

This request marks the order in idealo as shipped and adds trackingcode and carrier information to the order.

### `sendRevocationStatus($sIdealoOrderNr, $sReason, $sComment)` 

#### Parameters

`sIdealoOrderNr` - The order-nr you got from idealo in the "order_number" from the getOrders request
`sReason` - The reason of revocation - can be "CUSTOMER_REVOKE", "MERCHANT_DECLINE" or "RETOUR"
`sComment` (optional) - A 255 digit text with a comment from the merchant


For more information concerning the requests, have a look at the API documentation and developer guide.

## Error-handling

The client will return FALSE when any of the above listed requests failed with a CURL-error.

You can access the information to this error for logging purposes or whatever you need them for, with the following methods:

	$oClient->getCurlError()` // error-message from CURL
	$oClient->getCurlErrno()` // error-number from CURL

In any case you can get the HTTP status code from the last request with the following method:

	$oClient->getHttpStatus()

When this method returns 200 everything was ok with the last request.

In the idealo API documentation, you can find a list with the HTTP status error-codes and their meanings for every request.

### Logging

Errors will be logged to the default webserver error log.

### Testing

You can configure a direct link to a test-file filled with json-encoded orders like you would receive them directly from the API.
You have to enter the link in the "$sDebugDirectUrl" parameter in the idealo/Direktkauf/REST/Client.php file for example like this:
"http://*YOUR_SERVER_HERE*/order_test_file.txt"
