<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\Stripe\Message;

/**
 * Stripe Unstore Request
 */
class UnstoreRequest extends PurchaseRequest
{
    public function getData()
    {
        $this->validate('cardReference');

        return null;
    }

    public function getHttpMethod()
    {
        return 'DELETE';
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/customers/'.$this->getCardReference();
    }
}
