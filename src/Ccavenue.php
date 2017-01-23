<?php

namespace tinkers\ccavenue;

use tinkers\exceptions\CredentialNotSetException;
use tinkers\exceptions\InvalidIntegrationMethodException;

class CCAvenue
{

    const TYPE_BILLING_PAGE = "ccavenue_billing_page_non_seemless";

    const TYPE_IFRAME_CHECKOUT = "ccavenue_iframe_checkout";

    const TYPE_CHECKOUT_FORM = "ccavenue_checkout_form";

    public $method;

    protected $merchantId;

    protected $accessCode;

    protected $workingKey;

    public $baseUrl;

    private $paymentStatus;

    private $allowedMethods = [
        "ccavenue_billing_page_non_seemless",
        "ccavenue_iframe_checkout",
        "ccavenue_checkout_form",
    ];

    public function __construct ($method = null, $config = [], $test = false)
    {
        if (!$method)
            throw new InvalidIntegrationMethodException('Integration method must be specified');

        if (!in_array($method, $this->allowedMethods))
            throw new InvalidIntegrationMethodException('Invalid Integration method');

        $this->method = $method;

        if (isset($config['merchant_id']))
            $this->merchantId = $config['merchant_id'];

        if (isset($config['access_code']))
            $this->accessCode = $config['access_code'];

        if (isset($config['working_key']))
            $this->workingKey = $config['working_key'];

        if ($test)
            $this->baseUrl = 'https://secure.ccavenue.com';
        else
            $this->baseUrl = 'https://test.ccavenue.com';

        return $this;

    }

    public function setMerchantId ($value)
    {
        $this->merchantId = $value;
    }

    public function getMerchantId ()
    {
        if (empty($this->merchantId))
            throw new CredentialNotSetException('merchant id not provided');

        return $this->merchantId;
    }

    public function setAccessCode ($value)
    {
        $this->accessCode = $value;
    }

    public function getAccessCode ()
    {
        if (empty($this->accessCode))
            throw new CredentialNotSetException('access code not provided');

        return $this->accessCode;
    }

    public function setWorkingKey ($value)
    {
        $this->workingKey = $value;
    }

    public function getWorkingKey ()
    {
        if (empty($this->workingKey))
            throw new CredentialNotSetException('working key not provided');

        return $this->workingKey;
    }

    public function getPaymentStatus ()
    {
        return $this->paymentStatus;
    }

    public function requestGenerator ($requestData)
    {

        $merchantData = null;

        if ($this->method == self::TYPE_BILLING_PAGE || $this->method == self::TYPE_IFRAME_CHECKOUT) {
            foreach ($requestData as $key => $value) {
                $merchantData .= $key . '=' . $value . '&';
            }
        } elseif ($this->method == self::TYPE_CHECKOUT_FORM) {
            foreach ($requestData as $key => $value) {
                $merchantData .= $key . '=' . urlencode($value) . '&';
            }
        }

        $encryptedData = Crypto::encrypt($merchantData, $this->workingKey);

        return $this->method == self::TYPE_IFRAME_CHECKOUT ?
            [
                'encrypted_data' => $encryptedData,
                'access_code' => $this->accessCode,
                'form_action' => $this->baseUrl . '/transaction/transaction.do?command=initiateTransaction',
            ] : [
                'production_url' => $this->baseUrl . '/transaction/transaction.do?command=initiateTransaction&encRequest=' . $encryptedData . '&access_code=' . $this->accessCode
            ];
    }

    public function interpretStatus (ResponseHandler $response)
    {
        return $response['order_status'];
    }

}