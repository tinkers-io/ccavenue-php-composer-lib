<?php

namespace tinkers\ccavenue;

use tinkers\ccavenue\billingpage\BillingPage;
use tinkers\ccavenue\formcheckout\FormCheckout;
use tinkers\ccavenue\iframecheckout\IframeCheckout;
use tinkers\exceptions\InvalidIntegrationMethodException;

class CCAvenue
{

    const BILLING_PAGE = "ccavenue_billing_page_non_seemless";

    const IFRAME_CHECKOUT = "ccavenue_iframe_checkout";

    const CHECKOUT_FORM = "ccavenue_checkout_form";

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

        return $this->getInstance();

    }

    public function setMerchantId ($value)
    {
        $this->merchantId = $value;
    }

    public function getMerchantId ()
    {
        return $this->merchantId;
    }

    public function setAccessCode ($value)
    {
        $this->accessCode = $value;
    }

    public function getAccessCode ()
    {
        return $this->accessCode;
    }

    public function setWorkingKey ($value)
    {
        $this->workingKey = $value;
    }

    public function getWorkingKey ()
    {
        return $this->workingKey;
    }

    public function getPaymentStatus ()
    {
        return $this->paymentStatus;
    }

    protected function getInstance ()
    {
        switch ($this->method) {
            case self::BILLING_PAGE:
                return new BillingPage();
            case self::IFRAME_CHECKOUT:
                return new IframeCheckout();
            case self::CHECKOUT_FORM;
                return new FormCheckout();
            default:
                return new BillingPage();
        }
    }

    public function requestGenerator ($requestData)
    {

        $merchantData = null;

        if ($this->method == self::BILLING_PAGE || $this->method == self::IFRAME_CHECKOUT) {
            foreach ($requestData as $key => $value) {
                $merchantData .= $key . '=' . $value . '&';
            }
        } elseif ($this->method == self::CHECKOUT_FORM) {
            foreach ($requestData as $key => $value) {
                $merchantData .= $key . '=' . urlencode($value) . '&';
            }
        }

        $encryptedData = Crypto::encrypt($merchantData, $this->workingKey);

        return $this->method == self::IFRAME_CHECKOUT ?
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