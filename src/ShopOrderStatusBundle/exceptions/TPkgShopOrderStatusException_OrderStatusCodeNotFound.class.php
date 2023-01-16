<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopOrderStatusException_OrderStatusCodeNotFound extends TPkgCmsException_Log
{
    /**
     * @var string|null
     */
    private $statusCode = null;

    /**
     * @var string|null
     */
    private $shopId = null;

    /**
     * @param string|null $shopId
     *
     * @return $this
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param string|null $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $sString = parent::__toString();
        $sString = $sString."\n: Status Code ".$this->getStatusCode();
        $sString = $sString.' in shop '.$this->getShopId();

        return $sString;
    }
}
