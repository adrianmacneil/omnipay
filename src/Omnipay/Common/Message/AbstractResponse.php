<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\Common\Message;

use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Message\RequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpRedirectResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Abstract Response
 */
abstract class AbstractResponse implements ResponseInterface
{
    protected $request;
    protected $data;

    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function isRedirect()
    {
        return false;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMessage()
    {
        return null;
    }

    public function getTransactionReference()
    {
        return null;
    }

    /**
     * Automatically perform any required redirect
     */
    public function redirect()
    {
        if (!$this instanceof RedirectResponseInterface || !$this->isRedirect()) {
            throw new RuntimeException('This response does not support redirection.');
        }

        if ('GET' === $this->getRedirectMethod()) {
            return HttpRedirectResponse::create($this->getRedirectUrl())->send();
        } elseif ('POST' === $this->getRedirectMethod()) {
            $hiddenFields = implode(
                "\n",
                array_map(
                    function ($name, $value) {
                        return sprintf(
                            '<input type="hidden" name="%1$s" value="%2$s" />',
                            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                            htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
                        );
                    },
                    $this->getRedirectData()
                )
            );

            $output = <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="%1$s" method="post">
            <p>Redirecting to payment gateway...</p>
            <p>
                %2$s
                <input type="submit" value="Continue" />
            </p>
        </form>
    </body>
</html>';
EOF;
            $output = sprintf($output, htmlspecialchars($this->redirectUrl, ENT_QUOTES, 'UTF-8'), $hiddenFields);

            return HttpResponse::create($output)->send();
        }

        throw new RuntimeException("Unexpected redirect method '{$response->getRedirectMethod()}'");
    }
}
