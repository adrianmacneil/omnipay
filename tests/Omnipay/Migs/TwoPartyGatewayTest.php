<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\Migs;

use Omnipay\GatewayTestCase;
use Omnipay\Common\CreditCard;

class TwoPartyGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new TwoPartyGateway($this->getHttpClient(), $this->getHttpRequest());

        $this->gateway->setSecureHash(md5('example'));

        $this->options = array(
            'amount'        => 1000,
            'transactionId' => 12345,
            'card' => new CreditCard(
                array(
                    'number' => '4111111111111111',
                    'expiryMonth' => '05',
                    'expiryYear' => '2013',
                    'cvv' => '123',
                )
            ),
        );
    }

    public function testPurchaseSuccess()
    {
        $this->setMockHttpResponse('TwoPurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\Migs\Message\Response', $response);

        $this->assertTrue($response->isSuccessful());

        $this->assertEquals('12345', $response->getTransactionReference());

        $this->assertSame('Approved', $response->getMessage());
    }

    public function testPurchaseFailure()
    {
        $this->setMockHttpResponse('TwoPurchaseFailure.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\Migs\Message\Response', $response);

        $this->assertFalse($response->isSuccessful());

        $this->assertEquals('12345', $response->getTransactionReference());

        $this->assertEquals('Declined', $response->getMessage());
    }
}
