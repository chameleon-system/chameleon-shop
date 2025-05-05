<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPNException_OrderHasNoPaymentGroup extends AbstractPkgShopPaymentIPNException
{
    /**
     * @var string
     */
    private $orderId;

    /**
     * @param string $sOrderId
     * @param string $message
     * @param int $code
     */
    public function __construct(
        $sOrderId,
        $message = '',
        $code = 0,
        ?Exception $previous = null
    ) {
        $this->orderId = $sOrderId;
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        $sString = parent::__toString();
        $sString = $sString."\nOrder-ID: ".$this->getOrderId();

        return $sString;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
}
