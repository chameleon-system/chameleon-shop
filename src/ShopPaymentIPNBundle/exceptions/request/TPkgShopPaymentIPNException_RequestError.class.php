<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPNException_RequestError extends AbstractPkgShopPaymentIPNException
{
    /**
     * @var null|TPkgShopPaymentIPNRequest
     */
    private $request = null;

    /**
     * @param TPkgShopPaymentIPNRequest $oRequest
     * @param string                    $message
     * @param int                       $code
     * @param Exception                 $previous
     */
    public function __construct(
        TPkgShopPaymentIPNRequest $oRequest,
        $message = '',
        $code = 0,
        Exception $previous = null
    ) {
        $this->request = $oRequest;
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        $sString = parent::__toString();

        $sString = $sString."\nRequestURL: ".$this->getRequest()->getRequestURL()."\nRequestData: ".print_r(
            $this->getRequest()->getRequestPayload(),
            true
        );

        return $sString;
    }

    /**
     * the header to return to the caller.
     *
     * @return string
     */
    public function getResponseHeader()
    {
        return 'HTTP/1.0 403.10 Invalid configuration';
    }

    /**
     * @return null|TPkgShopPaymentIPNRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
}
