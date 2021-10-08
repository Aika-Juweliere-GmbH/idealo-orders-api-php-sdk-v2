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

class Clientv2
{

    //TODO: Add live url later
    protected const API_LIVE_URL = '';
    protected const API_TEST_URL = 'https://orders-sandbox.idealo.com/api/v2/';

    protected string $client;
    protected string $secret;

    protected bool $isLiveMode = false;

    protected string $authorizationToken = '';
    protected int $shopId;

    protected $iHttpStatus = null;
    protected $sCurlError = false;
    protected $iCurlErrno = false;

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

    /**
     * @param string $idealoOrderId
     *
     * @return array
     */
    public function getOrder(string $idealoOrderId): array
    {
        return $this->getJsonArrayFromRequest($this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders/' . $idealoOrderId);
    }

    /**
     * @return array
     */
    public function getNewOrder(): array
    {
        return $this->getJsonArrayFromRequest($this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/new-orders');
    }

    /**
     * @param string $idealoOrderId
     * @param string $merchantOrderNumber
     *
     * @return array
     */
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
     *
     * @return array
     *
     * TODO: Maybe set this function and setMerchantOrderNumber to void.
     */
    public function setFulfillmentInformation(string $idealoOrderId, string $carrier, array $trackingCode): array
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

    /**
     * @param string $idealoOrderId
     * @param string|null $sku
     * @param int $remainingQuantity
     * @param string $reason
     * @param string|null $comment
     *
     * @return array
     */
    public function setOrderRevoke(string $idealoOrderId, ?string $sku, int $remainingQuantity, string $reason, ?string $comment): array
    {
        //TODO: Can I send params with null value?
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
    public function setRefundForOrder(string $idealoOrderId, float $refundAmount, string $currency): array
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

    /**
     * @param string $idealoOrderId
     *
     * @return array
     */
    public function getRefunds(string $idealoOrderId): array
    {
        return $this->getJsonArrayFromRequest($this->getBaseUrlForApi() . 'shops/' . $this->getShopId() . '/orders/' . $idealoOrderId . '/refunds');
    }

    /**
     * Clientv2 setter
     *
     * @param string $client
     */
    protected function setClient(string $client): void
    {
        $this->client = $client;
    }

    /**
     * Clientv2 getter
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
    protected function setSecret(string $secret): void
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
    protected function setIsLiveMode(bool $isLive): void
    {
        $this->isLiveMode = $isLive;
    }

    /**
     * Is live mode getter
     *
     * @return bool
     */
    public function getIsLiveMode(): bool
    {
        return $this->isLiveMode;
    }

    /**
     * Authorization setter
     */
    protected function setAuthorization(): void
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
     *
     * @return array
     */
    protected function getJsonArrayFromRequest(string $baseUrl, bool $hasBody = false, bool $isBasicAuthorization = false, array $body = []): array
    {
        $sResponse = $this->sendCurlRequest($baseUrl, $hasBody, $isBasicAuthorization, false, $body);

        if (!$sResponse) {
            return [];
        }

        return (array) json_decode($sResponse, true);
    }

    /**
     * @return array
     */
    protected function getReportingHeaders(): array
    {
        $aHeaders = array();
        if ($this->getAuthorization() !== '') {
            $aHeaders[] = 'Authorization: ' . $this->getAuthorization();
        }

        return $aHeaders;
    }

    /**
     * @param $iHttpStatus
     */
    protected function setHttpStatus($iHttpStatus): void
    {
        $this->iHttpStatus = $iHttpStatus;
    }

    /**
     * @return int
     */
    public function getHttpStatus(): int
    {
        return $this->iHttpStatus;
    }

    /**
     * @param $sCurlError
     *
     * @return void
     */
    protected function setCurlError($sCurlError): void
    {
        $this->sCurlError = $sCurlError;
    }

    /**
     * @return bool
     */
    public function getCurlError(): bool
    {
        return $this->sCurlError;
    }

    /**
     * @param $iCurlErrno
     *
     * @return void
     */
    protected function setCurlErrno($iCurlErrno): void
    {
        $this->iCurlErrno = $iCurlErrno;
    }

    /**
     * @return bool
     */
    public function getCurlErrno(): bool
    {
        return $this->iCurlErrno;
    }

    /**
     * @return void
     */
    protected function resetStatusProperties(): void
    {
        $this->setHttpStatus(null);
        $this->setCurlErrno(false);
        $this->setCurlError(false);
    }

    protected function sendCurlRequest($url, $hasBody = false, bool $isBasicAuthorization = false, $isRetry = false, array $body = [])
    {
        //NOTICE: Delete old status properties
        $this->resetStatusProperties();

        $oCurl = curl_init($url);

        //NOTICE: Set headers for request
        $aHttpHeaders = $this->getReportingHeaders();

        //NOTICE: $hasBody is the body in requests
        if($hasBody !== false) {
            curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, "POST");
            if (!$isBasicAuthorization) {
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

        if($sResponse === false && $isRetry === false && $this->getCurlError() != '') {
            $sResponse = $this->sendCurlRequest($url, $hasBody, $isBasicAuthorization, true, $body);
        }

        if ( $this->getHttpStatus() != 200 ) {
            // API is down
            if ($this->getHttpStatus() == 502) {
                $this->setCurlError('API down');
            } elseif ($this->getHttpStatus() == 401) {
                $this->setCurlError('Unauthorized');
            }elseif ($this->getHttpStatus() == 400) {
                $this->setCurlError('Bad Request');
            } elseif ($this->getHttpStatus() == 409) {
                $this->setCurlError('Conflict');
            } else {
                $this->setCurlError('');
            }
            $sResponse = false;
        }

        //Only for set Merchant Order Number. Otherwise an empty array would be returned on success and error
        //TODO: Can we find a better solution for this?
        if ($this->getHttpStatus() == 204) {
            return $sResponse = '204';
        }

        return $sResponse;
    }
}