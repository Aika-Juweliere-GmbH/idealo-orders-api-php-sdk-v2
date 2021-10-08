# idealo Orders-API v2: PHP SDK
# Implementation Guide

___

## License and usage
This SDK can be used under the conditions of the Apache License 2.0, see LICENSE for details

___

## Technical requirements
- Standard Apache webserver with at least PHP 7.4
- The curl library for PHP

___

## Introduction

This is not an official library update for the Orders API v2 from Idealo, but from Uhrenlounge Dresden!

The implementation of the idealo SDK is  easy and straightforward.
Please test and integrate this SDK only with Idealo's sandbox environment. Generate an appropriate key on the Idealo Business page.
You have any question? Than write me a mail. tom.gottschlich@uhrenlounge.de

___

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

___

## Implementation

With this client object you have direct access to all the v2 REST-API functions from idealo.

At the moment there the following 10 requests available (5 more than in the v1 REST-API from idealo): 

___

### `$oClient->getOrders(): array`

Requests all orders from idealo.
They are delivered as an associative array, directly like idealo delivers them in the following format:

    {
        "content" : [ {
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
                "created" : "2021-09-08T09:43:33.666273Z",
                "updated" : "2021-09-08T09:43:33.666273Z"
            } ],
            "voucher" : {
                "code" : "FXWFGE (30%, max. 5 EUR)"
            }
        } ],
        "totalElements" : 1,
        "totalPages" : 1
    }

___

### `$oClient->getOrder(string $idealoOrderId): array`

Get a specific order by idealoOrderId:

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
            "created" : "2021-09-08T09:43:34.090744Z",
            "updated" : "2021-09-08T09:43:34.090744Z"
        } ],
        "voucher" : {
            "code" : "FXWFGE (30%, max. 5 EUR)"
        }
    }

___

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

### `$oClient->setMerchantOrderNumber(string $idealoOrderId, string $merchantOrderNumber): array`

    No body returned for response

___

### `$oClient->setFulfillmentInformation(string $idealoOrderId, string $carrier, array $trackingCode): array`

    No body returned for response

___

### `$oClient->setOrderRevoke(string $idealoOrderId, ?string $sku, int $remainingQuantity, string $reason, ?string $comment): array`

    No body returned for response

___

### `$oClient->setRefundForOrder(string $idealoOrderId, float $refundAmount, string $currency): array`

    No body returned for response

####Example response for invalid requests

    {
        "type" : "about:blank",
        "title" : "This order is not refundable as it was not paid using 'IDEALO_CHECKOUT_PAYMENTS'.",
        "instance" : "https://orders.idealo.com/api/v2/shops/12345/orders/A1B2C3D4/refunds",
        "reason" : "ORDER_NOT_PAID_USING_IDEALO_CHECKOUT_PAYMENTS"
    }

Reason | Description
--- | ---- |
ORDER_NOT_PAID_USING_IDEALO_CHECKOUT_PAYMENTS | Occurs if the order has not been paid by IDEALO_CHECKOUT_PAYMENTS
REFUND_PERIOD_EXCEEDED | Occurs if the order is older than 60 days and has been in the state COMPLETED.
REFUND_AMOUNT_EXCEEDS_ORDER_PRICE | Occurs if the sum of all refunds exceeds the total price of the order

___

### `$oClient->getRefunds(string $idealoOrderId): array`

    [ 
        {
            "refundId" : "example-refund-id",
            "refundTransactionId" : "example-refund-transaction-id",
            "status" : "OPEN",
            "currency" : "EUR",
            "refundAmount" : 1.99,
            "created" : "2021-09-08T09:41:39.887112Z",
            "updated" : "2021-09-08T09:41:39.887115Z"
        } 
    ]

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
