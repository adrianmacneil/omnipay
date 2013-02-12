<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\Billing\CardSave;

use Mockery as m;
use Omnipay\CreditCard;
use Omnipay\BaseGatewayTest;
use Omnipay\Request;

class GatewayTest extends BaseGatewayTest
{
    public function setUp()
    {
        $this->httpClient = m::mock('\Omnipay\HttpClient\HttpClientInterface');
        $this->httpRequest = m::mock('\Symfony\Component\HttpFoundation\Request');

        $this->gateway = new Gateway($this->httpClient, $this->httpRequest);

        $this->options = array(
            'amount' => 1000,
            'returnUrl' => 'https://www.example.com/return',
            'card' => new CreditCard(array(
                'firstName' => 'Example',
                'lastName' => 'User',
                'number' => '4111111111111111',
                'expiryMonth' => '12',
                'expiryYear' => '2016',
                'cvv' => '123',
            )),
        );
    }

    public function testPurchase()
    {
        $this->httpClient->shouldReceive('post')
            ->with('https://gw1.cardsaveonlinepayments.com:4430/', m::type('string'), m::type('array'))->once()
            ->andReturn('<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><CardDetailsTransactionResponse xmlns="https://www.thepaymentgateway.net/"><CardDetailsTransactionResult AuthorisationAttempted="True"><StatusCode>0</StatusCode><Message>AuthCode: 971112</Message></CardDetailsTransactionResult><TransactionOutputData CrossReference="130114063233159001702222"><AuthCode>971112</AuthCode><ThreeDSecureAuthenticationCheckResult>NOT_ENROLLED</ThreeDSecureAuthenticationCheckResult><GatewayEntryPoints><GatewayEntryPoint EntryPointURL="https://gw1.cardsaveonlinepayments.com:4430/" Metric="100" /><GatewayEntryPoint EntryPointURL="https://gw2.cardsaveonlinepayments.com:4430/" Metric="200" /></GatewayEntryPoints></TransactionOutputData></CardDetailsTransactionResponse></soap:Body></soap:Envelope>');

        $response = $this->gateway->purchase($this->options);

        $this->assertInstanceOf('\Omnipay\Billing\CardSave\Response', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('130114063233159001702222', $response->getGatewayReference());
    }

    public function testPurchaseError()
    {
        $this->httpClient->shouldReceive('post')
            ->with('https://gw1.cardsaveonlinepayments.com:4430/', m::type('string'), m::type('array'))->once()
            ->andReturn('<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><CardDetailsTransactionResponse xmlns="https://www.thepaymentgateway.net/"><CardDetailsTransactionResult AuthorisationAttempted="False"><StatusCode>30</StatusCode><Message>Input variable errors</Message><ErrorMessages><MessageDetail><Detail>Required variable (PaymentMessage.TransactionDetails.OrderID) is missing</Detail></MessageDetail></ErrorMessages></CardDetailsTransactionResult><TransactionOutputData /></CardDetailsTransactionResponse></soap:Body></soap:Envelope>');

        $response = $this->gateway->purchase($this->options);

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('Input variable errors', $response->getMessage());
    }
}
