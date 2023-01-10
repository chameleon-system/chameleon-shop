<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPNException_InvalidIP extends TPkgShopPaymentIPNException_RequestError
{
    /** @var string */
    private $requestIP = '';

    /**
     * @param string                    $sRequestIP
     * @param TPkgShopPaymentIPNRequest $oRequest
     * @param string                    $message
     * @param int                       $code
     * @param Exception|null            $previous
     */
    public function __construct(
        $sRequestIP,
        TPkgShopPaymentIPNRequest $oRequest,
        $message = '',
        $code = 0,
        Exception $previous = null
    ) {
        $this->requestIP = $sRequestIP;
        parent::__construct($oRequest, $message, $code, $previous);
    }

    public function __toString(): string
    {
        $sString = parent::__toString();
        $sString = $sString."\nRequest IP: ".$this->getRequestIP();

        return $sString;
    }

    /**
     * the header to return to the caller.
     *
     * @return string
     */
    public function getResponseHeader()
    {
        return 'HTTP/1.0 403.6 IP address rejected';
    }

    /**
     * @return string
     */
    public function getRequestIP()
    {
        return $this->requestIP;
    }
}
