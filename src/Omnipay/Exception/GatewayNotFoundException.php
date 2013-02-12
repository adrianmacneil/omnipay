<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\Exception;

use Omnipay\Exception;

/**
 * Gateway Not Found exception.
 *
 * Thrown by GatewayFactory when trying to create a gateway which is not available
 */
class GatewayNotFoundException extends Exception
{
}
