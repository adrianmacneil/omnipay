<?php

/*
 * This file is part of the Tala Payments package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tala\Billing\PaymentExpress;

use BadMethodCallException;
use SimpleXMLElement;
use Tala\AbstractGateway;
use Tala\Exception\InvalidResponseException;
use Tala\RedirectResponse;
use Tala\Request;

/**
 * DPS PaymentExpress PxPay Gateway
 */
class PxPayGateway extends AbstractGateway
{
    protected $endpoint = 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx';

    public function getDefaultSettings()
    {
        return array(
            'username' => '',
            'password' => '',
        );
    }

    public function authorize(Request $request, $source)
    {
        $data = $this->buildPurchase($request, $source);
        $data->TxnType = 'Auth';

        return $this->sendPurchase($data);
    }

    public function completeAuthorize(Request $request)
    {
        return $this->completePurchase($request);
    }

    public function purchase(Request $request, $source)
    {
        $data = $this->buildPurchase($request, $source);
        $data->TxnType = 'Purchase';

        return $this->sendPurchase($data);
    }

    public function completePurchase(Request $request)
    {
        $result = $this->getHttpRequest()->get('result');
        if (empty($result)) {
            throw new InvalidResponseException;
        }

        // validate dps response
        $data = new SimpleXMLElement('<ProcessResponse/>');
        $data->PxPayUserId = $this->username;
        $data->PxPayKey = $this->password;
        $data->Response = $result;

        return $this->sendComplete($data);
    }

    protected function buildPurchase(Request $request, $source)
    {
        $request->validateRequired(array('amount', 'returnUrl'));

        $data = new SimpleXMLElement('<GenerateRequest/>');
        $data->PxPayUserId = $this->username;
        $data->PxPayKey = $this->password;
        $data->AmountInput = $request->amountDollars;
        $data->CurrencyInput = $request->currency;
        $data->MerchantReference = $request->description;
        $data->UrlSuccess = $request->returnUrl;
        $data->UrlFail = $request->returnUrl;

        return $data;
    }

    protected function sendPurchase($data)
    {
        $response = $this->getHttpClient()->post($this->endpoint, $data->asXML());
        $xml = new SimpleXMLElement($response);

        if ((string) $xml['valid'] == '1') {
            return new RedirectResponse((string) $xml->URI);
        } else {
            throw new InvalidResponseException;
        }
    }

    protected function sendComplete($data)
    {
        $response = $this->getHttpClient()->post($this->endpoint, $data->asXML());

        return new Response($response);
    }

    /**
     * {@inheritdoc}
     */
    public function capture(Request $request)
    {
        throw new BadMethodCallException();
    }

    /**
     * {@inheritdoc}
     */
    public function refund(Request $request)
    {
        throw new BadMethodCallException();
    }

    /**
     * {@inheritdoc}
     */
    public function void(Request $request)
    {
        throw new BadMethodCallException();
    }
}
