<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\TargetPay\Message;

class IdealPurchaseRequest extends PurchaseRequest
{
    public function getIssuer()
    {
        return $this->getParameter('issuer');
    }

    public function setIssuer($value)
    {
        return $this->setParameter('issuer', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->validate('issuer', 'amount', 'description', 'returnUrl');

        return array(
            'rtlo' => $this->getSubAccountId(),
            'bank' => $this->getIssuer(),
            'description' => $this->getDescription(),
            'amount' => $this->getAmountInteger(),
            'language' => $this->getLanguage(),
            'currency' => $this->getCurrency(),
            'returnurl' => $this->getReturnUrl(),
            'reporturl' => $this->getNotifyUrl(),
        );
    }
}
