<?php

namespace Omnipay\Adyen\Message;

use Omnipay\TestCase;

class PurchaseRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(array(
            'merchantAccount' => 'BidZoneNL',
            'merchantReference' => 'TEST-10000',
            'secret' => 'test',
            'skinCode' => '05cp1ZtM',
            'amount' => 10.00,
            'currencyCode' => 'EUR',
            'testMode' => true,
            'shipBeforeDate' => '2013-11-11',
            'sessionValidity' => '2013-11-05T11:27:59'
        ));
    }

    public function testGetData()
    {
        $this->request->initialize(array(
            'merchantAccount' => 'BidZoneNL',
            'merchantReference' => 'TEST-10000',
            'secret' => 'test',
            'skinCode' => '05cp1ZtM',
            'amount' => 10.00,
            'currencyCode' => 'EUR',
            'testMode' => true,
            'shipBeforeDate' => '2013-11-11',
            'sessionValidity' => '2013-11-05T11:27:59'
        ));

        $data = $this->request->getData();

        $this->assertSame('BidZoneNL', $data['merchantAccount']);
        $this->assertSame(1000, $data['amount']);
        $this->assertSame('EUR', $data['currencyCode']);
        $this->assertSame('TEST-10000', $data['merchantReference']);
        $this->assertSame('dxrViSKHSFX8EkqG8RwQ9ei5R+4=', $data['merchantSig']);
    }

    public function testGenerateSignature()
    {
        $this->request->setSecret('test');
        $data = array(
            'amount' => 1000,
            'currencyCode' => 'EUR',
            'shipBeforeDate' => '2013-11-11',
            'merchantReference' => 'TEST-10000',
            'skinCode' => '05cp1ZtM',
            'merchantAccount' => 'BidZoneNL',
            'sessionValidity' => '2013-11-05T11:27:59'
        );

        $this->assertSame('dxrViSKHSFX8EkqG8RwQ9ei5R+4=', $this->request->generateSignature($data));
    }

    public function testSend()
    {
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertSame('POST', $response->getRedirectMethod());
        $this->assertSame($this->request->getData(), $response->getRedirectData());
    }
}
