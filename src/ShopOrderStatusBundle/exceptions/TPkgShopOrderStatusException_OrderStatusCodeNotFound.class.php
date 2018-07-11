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
    private $statusCode = null;
    private $shopId = null;

    /**
     * @param null $shopId
     *
     * @return $this
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param null $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function __toString()
    {
        $sString = parent::__toString();
        $sString = $sString."\n: Status Code ".$this->getStatusCode();
        $sString = $sString.' in shop '.$this->getShopId();

        return $sString;
    }
}
