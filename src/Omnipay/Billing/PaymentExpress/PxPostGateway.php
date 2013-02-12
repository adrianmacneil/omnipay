<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\Billing\PaymentExpress;

use Omnipay\AbstractGateway;
use Omnipay\Request;

/**
 * DPS PaymentExpress PxPost Gateway
 */
class PxPostGateway extends AbstractGateway
{
    protected $endpoint = 'https://sec.paymentexpress.com/pxpost.aspx';
    protected $username;
    protected $password;

    public function getName()
    {
        return 'PaymentExpress PxPost';
    }

    public function defineSettings()
    {
        return array(
            'username' => '',
            'password' => '',
        );
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($value)
    {
        $this->username = $value;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function authorize($options)
    {
        $data = $this->buildAuthorizeOrPurchase($options, 'Auth');

        return $this->send($data);
    }

    public function capture($options)
    {
        $data = $this->buildCaptureOrRefund($options, 'Complete');

        return $this->send($data);
    }

    public function purchase($options)
    {
        $data = $this->buildAuthorizeOrPurchase($options, 'Purchase');

        return $this->send($data);
    }

    public function refund($options)
    {
        $data = $this->buildCaptureOrRefund($options, 'Refund');

        return $this->send($data);
    }

    protected function buildAuthorizeOrPurchase($options, $method)
    {
        $request = new Request($options);
        $request->validate(array('amount'));
        $source = $request->getCard();
        $source->validate();

        $data = new \SimpleXMLElement('<Txn />');
        $data->PostUsername = $this->username;
        $data->PostPassword = $this->password;
        $data->TxnType = $method;
        $data->CardNumber = $source->getNumber();
        $data->CardHolderName = $source->getName();
        $data->Amount = $request->getAmountDollars();
        $data->DateExpiry = $source->getExpiryDate('my');
        $data->Cvc2 = $source->getCvv();
        $data->InputCurrency = $request->getCurrency();
        $data->MerchantReference = $request->getDescription();

        return $data;
    }

    protected function buildCaptureOrRefund($options, $method)
    {
        $request = new Request($options);
        $request->validate(array('gatewayReference', 'amount'));

        $data = new \SimpleXMLElement('<Txn />');
        $data->PostUsername = $this->username;
        $data->PostPassword = $this->password;
        $data->TxnType = $method;
        $data->DpsTxnRef = $request->getGatewayReference();
        $data->Amount = $request->getAmountDollars();

        return $data;
    }

    protected function send($data)
    {
        $response = $this->httpClient->post($this->endpoint, $data->asXML());

        return new Response($response);
    }
}
