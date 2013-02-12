<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay;

/**
 * Form Redirect Response class
 */
class FormRedirectResponse extends RedirectResponse
{
    protected $redirectUrl;
    protected $formData;

    /**
     * Constructor.
     *
     * @param string $url      The URL to redirect to
     * @param array  $formData Optional form POST data
     */
    public function __construct($url, $formData = array())
    {
        $this->redirectUrl = $url;
        $this->formData = $formData;
    }

    /**
     * Gets the form POST data
     *
     * @return array The form POST data
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * Perform the required redirect.
     */
    public function redirect($httpResponseClass = '\Symfony\Component\HttpFoundation\Response')
    {
        $hiddenFields = '';
        foreach ($this->formData as $name => $value) {
            $hiddenFields .= sprintf(
                '<input type="hidden" name="%1$s" value="%2$s" />',
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            );
        }

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

        $response = new $httpResponseClass($output);
        $response->send();
    }
}
