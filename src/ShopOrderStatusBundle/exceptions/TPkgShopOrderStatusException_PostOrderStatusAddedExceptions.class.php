<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopOrderStatusException_PostOrderStatusAddedExceptions extends TPkgCmsException
{
    /**
     * @var TPkgCmsException[]
     */
    private $exceptionList = array();

    /**
     * @var TdbShopOrderStatus
     */
    private $orderStatus = null;

    /**
     * @param TdbShopOrderStatus $orderStatus
     *
     * @return $this
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * @return TdbShopOrderStatus
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @param TPkgCmsException[] $exceptionList
     *
     * @return $this
     */
    public function setExceptionList($exceptionList)
    {
        $this->exceptionList = $exceptionList;

        return $this;
    }

    /**
     * @return TPkgCmsException[]
     */
    public function getExceptionList()
    {
        return $this->exceptionList;
    }

    public function __toString()
    {
        $sString = parent::__toString();
        $sString = $sString."\n: Status Entry ".$this->getOrderStatus()->sqlData;

        $sString .= "\nExceptionList: ";
        foreach ($this->getExceptionList() as $exception) {
            $sString .= "\n".$exception;
        }

        return $sString;
    }
}
