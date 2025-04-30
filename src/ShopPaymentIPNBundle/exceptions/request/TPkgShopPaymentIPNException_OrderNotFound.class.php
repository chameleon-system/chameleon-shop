<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPNException_OrderNotFound extends TPkgShopPaymentIPNException_RequestError
{
    /** @var string|null */
    private $orderCmsIdent = '';

    /**
     * @param string|null $iOrderCmsIdent
     * @param string $message
     * @param int $code
     */
    public function __construct(
        $iOrderCmsIdent,
        TPkgShopPaymentIPNRequest $oRequest,
        $message = '',
        $code = 0,
        ?Exception $previous = null
    ) {
        $this->orderCmsIdent = $iOrderCmsIdent;
        parent::__construct($oRequest, $message, $code, $previous);
    }

    public function __toString(): string
    {
        $sString = parent::__toString();
        $sString = $sString."\nOrderCmsIdent: ".$this->getOrderCmsIdent();

        return $sString;
    }

    /**
     * the header to return to the caller.
     *
     * @return string
     */
    public function getResponseHeader()
    {
        return 'HTTP/1.0 404 Not Found';
    }

    /**
     * @return string|null
     */
    public function getOrderCmsIdent()
    {
        return $this->orderCmsIdent;
    }
}
