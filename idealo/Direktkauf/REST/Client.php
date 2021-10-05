<?php
/*
   Copyright 2015 idealo internet GmbH

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/


namespace idealo\Direktkauf\REST;

class Client
{

    //TODO: Add live url later
    protected const API_LIVE_URL = '';
    protected const API_TEST_URL = 'https://orders-sandbox.idealo.com/api/v2/';
    
    /**
     * You can enter a URL to a test file with order-data here
     * This will be used to bypass the idealo API for testing purposes
     * Handle with caution!
     * Testfile needs to be utf8 encoded
     * Will not be used if set to false
     * 
     * @var string
     */
    protected $sDebugDirectUrl = false;

    protected string $client;
    protected string $secret;

    protected bool $isLiveMode = false;

    protected string $authorizationToken = '';
    protected int $shopId;

    protected $iHttpStatus = null;
    protected $sCurlError = false;
    protected $iCurlErrno = false;

    protected $sERPShopSystem = null;
    protected $sERPShopSystemVersion = null;
    protected $sIntegrationPartner = null;
    protected $sInterfaceVersion = null;

    protected $sAuthorization = null;

    //That are all urls after the API_LIVE_URL or API_TEST_URL:
    const URL_TYPE_GET_ORDERS = 'getOrders';
    const URL_TYPE_GET_SUPPORTED_PAYMENT_TYPES = 'getSupportedPaymentTypes';
    const URL_TYPE_SEND_ORDER_NR = 'sendOrderNr';
    const URL_TYPE_SEND_FULFILLMENT = 'sendFulfillmentStatus';
    const URL_TYPE_SEND_REVOCATION = 'sendRevocationStatus';

    /**
     * @param string $client
     * @param string $secret
     * @param bool $isLive
     */
    public function __construct(string $client, string $secret, bool $isLive = false)
    {
        $this->setClient($client);
        $this->setSecret($secret);
        $this->setIsLiveMode($isLive);

        $this->setAuthorization();
    }

    /**
     * Get all Orders for the shop
     *
     * @return array
     */
    public function getOrders(): array
    {
        return $this->getJsonArrayFromRequest($this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders');
    }

    public function getOrder(string $idealoOrderId): array
    {
        return $this->getJsonArrayFromRequest($this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders/' . $idealoOrderId);
    }

    public function getNewOrder(): array
    {
        return $this->getJsonArrayFromRequest($this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/new-orders');
    }

    public function setMerchantOrderNumber(string $idealoOrderId, string $merchantOrderNumber): array
    {
        return $this->getJsonArrayFromRequest(
            $this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders/' . $idealoOrderId . '/merchant-order-number',
            true,
            false,
            [
                'merchantOrderNumber' => $merchantOrderNumber
            ]
        );
    }

    /**
     * @param string $idealoOrderId
     * @param string $carrier
     * @param array $trackingCode
     * @return array
     *
     * TODO: Maybe set this function and setMerchantOrderNumber to void.
     */
    public function setFulfillmentInformation(string $idealoOrderId, string $carrier, array $trackingCode)
    {
        return $this->getJsonArrayFromRequest(
            $this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders/' . $idealoOrderId . '/fulfillment',
            true,
            false,
            [
                'carrier' => $carrier,
                'trackingCode' => $trackingCode
            ]
        );
    }

    public function setOrderRevoke(string $idealoOrderId, ?string $sku, int $remainingQuantity, string $reason, ?string $comment)
    {
        return $this->getJsonArrayFromRequest(
            $this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders/' . $idealoOrderId . '/fulfillment',
            true,
            false,
            [
                'sku'               => $sku,
                'remainingQuantity' => $remainingQuantity,
                'reason'            => $reason,
                'comment'           => $comment
            ]
        );
    }

    /**
     * @param string $idealoOrderId
     * @param float $refundAmount - Must be at least 0.01.
     * @param string $currency - ONLY ISO 4217 currency code!
     * TODO: Find a way to check this param or you check it in your own code.
     * TODO: Maybe change the name
     * @return array
     */
    public function setRefundForOrder(string $idealoOrderId, float $refundAmount, string $currency)
    {
        if ($refundAmount < 0.01) {
            return [];
        }
        return $this->getJsonArrayFromRequest(
            $this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders/' . $idealoOrderId . '/refunds',
            true,
            false,
            [
                'refundAmount' => $refundAmount,
                'currency' => $currency
            ]
        );
    }

    public function getRefunds(string $idealoOrderId): array
    {
        return $this->getJsonArrayFromRequest($this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders/' . $idealoOrderId . '/refunds');
    }

    /**
     * Client setter
     *
     * @param string $client
     */
    protected function setClient(string $client)
    {
        $this->client = $client;
    }

    /**
     * Client getter
     *
     * @return string
     */
    protected function getClient(): string
    {
        return $this->client;
    }

    /**
     * Secret setter
     *
     * @param string $secret
     */
    protected function setSecret(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * Secret getter
     *
     * @return string
     */
    protected function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Is live mode setter
     *
     * @param bool $isLive
     */
    protected function setIsLiveMode(bool $isLive)
    {
        $this->isLiveMode = $isLive;
    }

    /**
     * Is live mode getter
     *
     * @return bool
     */
    protected function getIsLiveMode(): bool
    {
        return $this->isLiveMode;
    }

    /**
     * Authorization setter
     */
    protected function setAuthorization()
    {
        $token = $this->getJsonArrayFromRequest($this->getBaseUrlForApi() . 'oauth/token', true, true);
        $this->authorizationToken = ucfirst($token['token_type']) . ' ' . $token['access_token'];
        $this->shopId = $token['shop_id'];
    }

    /**
     * Authorization getter
     *
     * @return string
     */
    protected function getAuthorization(): string
    {
        return $this->authorizationToken;
    }

    /**
     * Shop id getter
     *
     * @return int
     */
    protected function getShopId(): int
    {
        return $this->shopId;
    }

    /**
     * Get base URL for API curl requests. (Example: https://orders-sandbox.idealo.com/api/v2/ for sandbox)
     *
     * @return string
     */
    protected function getBaseUrlForApi(): string
    {
        return ($this->getIsLiveMode()) ? self::API_LIVE_URL : self::API_TEST_URL;
    }

    /**
     * Get JSON Array with the response of the API.
     *
     * @param string $baseUrl
     * @param bool $hasBody
     * @param bool $isBasicAuthorization
     * @param array $body
     * @return array
     */
    protected function getJsonArrayFromRequest(string $baseUrl, bool $hasBody = false, bool $isBasicAuthorization = false, array $body = [])
    {
        $sResponse = $this->sendCurlToAPIv2Request($baseUrl, $hasBody, $isBasicAuthorization, false, $body);

        if (!$sResponse) {
            return [];
        }

        return (array) json_decode($sResponse, true);
    }

    protected function getReportingHeaders()
    {
        $aHeaders = array();
        if ($this->getAuthorization() !== '') {
            $aHeaders[] = 'Authorization: ' . $this->getAuthorization();
        }

        return $aHeaders;
    }

    protected function sendCurlToAPIv2Request($sUrl, $hasBody = false, bool $isBasicAuthorization = false, $blIsRetry = false, array $body = [])
    {
        //NOTICE: Delete old status properties
        $this->resetStatusProperties();

        $oCurl = curl_init($sUrl);

        //NOTICE: Set headers for request
        $aHttpHeaders = $this->getReportingHeaders();

        //NOTICE: $hasBody is the body in requests
        if($hasBody !== false) {
            curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, "POST");
            //TODO: I dont think that is adding to header
            if ($isBasicAuthorization === false) {
                array_push($aHttpHeaders, 'Content-Type: application/json');
            }
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($body));
        } else {
            curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, "GET");
        }

        //NOTICE: Set headers to request
        if (!empty($aHttpHeaders)) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $aHttpHeaders);
        }

        curl_setopt($oCurl, CURLOPT_TIMEOUT, 60); //timeout in seconds
        curl_setopt($oCurl, CURLOPT_HEADER, false);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        if ($isBasicAuthorization) {
            curl_setopt($oCurl, CURLOPT_USERPWD, $this->getClient() . ':' . $this->getSecret());
        }

        //NOTICE: Here we send the curl request
        $sResponse = curl_exec($oCurl);

        $this->setHttpStatus(curl_getinfo($oCurl, CURLINFO_HTTP_CODE));

        if(curl_error($oCurl) != '') {
            $this->setCurlError(curl_error($oCurl));
            $this->setCurlErrno(curl_errno($oCurl));
        }

        curl_close($oCurl);

        //TODO: Is that a infiniti loop?
        if($sResponse === false && $blIsRetry === false && $this->getCurlError() != '') {
            $sResponse = $this->sendCurlToAPIv2Request($sUrl, $hasBody, $isBasicAuthorization, true, $body);
        }

        if ( $this->getHttpStatus() != '200' ) {
            // API is down
            if ($this->getHttpStatus() == '502') {
                $this->setCurlError('API down');
            } elseif ($this->getHttpStatus() == '401') {
                $this->setCurlError('Unauthorized');
            }elseif ($this->getHttpStatus() == '400') {
                $this->setCurlError('Bad Request');
            } elseif ($this->getHttpStatus() == '409') {
                $this->setCurlError('Conflict');
            } else {
                $this->setCurlError('');
            }
            $sResponse = false;
        }

        //Only for set Merchant Order Number. Otherwise an empty array would be returned on success and error
        //TODO: Can we find a better solution for this?
        if ($this->getHttpStatus() == '204') {
            return $sResponse = '204';
        }

        return $sResponse;
    }











    
    protected function setHttpStatus($iHttpStatus) 
    {
        $this->iHttpStatus = $iHttpStatus;
    }
    
    public function getHttpStatus() 
    {
        return $this->iHttpStatus;
    }
    
    protected function setCurlError($sCurlError)
    {
        $this->sCurlError = $sCurlError;
    }
    
    public function getCurlError() 
    {
        return $this->sCurlError;
    }
    
    protected function setCurlErrno($iCurlErrno)
    {
        $this->iCurlErrno = $iCurlErrno;
    }
    
    public function getCurlErrno() 
    {
        return $this->iCurlErrno;
    }

    public function setERPShopSystem($sERPShopSystem)
    {
        $this->sERPShopSystem = $sERPShopSystem;
    }

    public function getERPShopSystem()
    {
        return $this->sERPShopSystem;
    }

    public function setERPShopSystemVersion($sERPShopSystemVersion)
    {
        $this->sERPShopSystemVersion = $sERPShopSystemVersion;
    }

    public function getERPShopSystemVersion()
    {
        return $this->sERPShopSystemVersion;
    }

    public function setIntegrationPartner($sIntegrationPartner)
    {
        $this->sIntegrationPartner = $sIntegrationPartner;
    }

    public function getIntegrationPartner()
    {
        return $this->sIntegrationPartner;
    }

    public function setInterfaceVersion($sInterfaceVersion)
    {
        $this->sInterfaceVersion = $sInterfaceVersion;
    }

    public function getInterfaceVersion()
    {
        return $this->sInterfaceVersion;
    }
    
    public function getSupportedPaymentTypes()
    {   
        $sUrl = $this->getRequestUrl(self::URL_TYPE_GET_SUPPORTED_PAYMENT_TYPES);
        $aPaymentsTypes = $this->getJsonArrayFromRequest($sUrl);
        return $aPaymentsTypes;
    }
    
    public function sendOrderNr($sIdealoOrderNr, $sShopOrderNr)
    {
        $sUrl = $this->getRequestUrl(self::URL_TYPE_SEND_ORDER_NR, $sIdealoOrderNr);
        $aParams = array(
            'merchant_order_no' => $sShopOrderNr,
        );
        return $this->sendCurlRequest($sUrl, $aParams);
    }
    
    public function sendFulfillmentStatus($sIdealoOrderNr, $sTrackingCode = '', $sCarrier = '')
    {
        $sUrl = $this->getRequestUrl(self::URL_TYPE_SEND_FULFILLMENT, $sIdealoOrderNr);
        $aParams = array();
        if(!empty($sTrackingCode)) {
            $aParams['tracking_number'] = $sTrackingCode;
            $aParams['carrier'] = $sCarrier;
        }
        return $this->sendCurlRequest($sUrl, $aParams);
    }
    
    public function sendRevocationStatus($sIdealoOrderNr, $sReason, $sComment = false)
    {
        $sUrl = $this->getRequestUrl(self::URL_TYPE_SEND_REVOCATION, $sIdealoOrderNr);
        $aParams = array();
        $aParams['reason'] = $sReason;
        if($sComment !== false) {
            $aParams['comment'] = $sComment;
        }
        return $this->sendCurlRequest($sUrl, $aParams);
    }


    //TODO Hier weiter machen!
    
    protected function resetStatusProperties()
    {
        $this->setHttpStatus(null);
        $this->setCurlErrno(false);
        $this->setCurlError(false);
    }
    
    protected function sendCurlRequest($sUrl, $aParams = false, $blIsRetry = false) 
    {
        $this->resetStatusProperties();

        $oCurl = curl_init($sUrl);

        $aHttpHeaders = $this->getReportingHeaders();
        if($aParams !== false) {
            curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, "PUT");
            $aHttpHeaders[] = 'Content-Type: application/json';
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($aParams));
        } else {
            curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, "GET");
        }
        if (!empty($aHttpHeaders)) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $aHttpHeaders);
        }
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 60); //timeout in seconds
        curl_setopt($oCurl, CURLOPT_HEADER, false);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        
        $sResponse = curl_exec($oCurl);

        $this->setHttpStatus(curl_getinfo($oCurl, CURLINFO_HTTP_CODE));     
        
        if(curl_error($oCurl) != '') {
            $this->setCurlError(curl_error($oCurl));
            $this->setCurlErrno(curl_errno($oCurl));
        }

        curl_close($oCurl);
        
        if($sResponse === false && $blIsRetry === false && $this->getCurlError() != '') {
            $sResponse = $this->sendCurlRequest($sUrl, $aParams, true);
        }
        
        if ( $this->getHttpStatus() != '200' ) {
            // API is down
            if ($this->getHttpStatus() == '502') {
                $this->setCurlError('API down');
            } elseif ($this->getHttpStatus() == '401') {
                $this->setCurlError('Unauthorized');
            } else {
                $this->setCurlError('');
            }
            $sResponse = false;
        }
        return $sResponse;
    }

}