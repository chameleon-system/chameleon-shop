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
     * @var TPkgShopPaymentIPNRequest|null
     */
    private $request;

    /**
     * @param string $message
     * @param int $code
     */
    public function __construct(
        TPkgShopPaymentIPNRequest $oRequest,
        $message = '',
        $code = 0,
        ?Exception $previous = null
    ) {
        $this->request = $oRequest;
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
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
     * @return TPkgShopPaymentIPNRequest|null
     */
    public function getRequest()
    {
        return $this->request;
    }
}
