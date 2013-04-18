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

class ThreePartyGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new ThreePartyGateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'amount'        => 1000,
            'transactionId' => 12345,
            'returnUrl'     => 'https://www.example.com/return',
        );
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase(array('amount' => 1000));

        $this->assertInstanceOf('\Omnipay\Migs\Message\ThreePurchaseRequest', $request);

        $this->assertSame(1000, $request->getAmount());
    }

    public function testCompletePurchase()
    {
        $request = $this->gateway->completePurchase(array('amount' => 1000));

        $this->assertInstanceOf('\Omnipay\Migs\Message\ThreeCompletePurchaseRequest', $request);

        $this->assertSame(1000, $request->getAmount());
    }
}
