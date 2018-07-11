<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\IPN;

class AmazonPaymentIPNInvalidException extends \TPkgShopPaymentIPNException_RequestError
{
    /**
     * the header to return to the caller.
     *
     * @return string
     */
    public function getResponseHeader()
    {
        return 'HTTP/1.1 503 Service Unavailable';
    }
}
