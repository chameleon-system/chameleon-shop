<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopBasketArticleCustomDataValidationError
{
    private $itemName = null;
    private $errorMessageCode = null;
    private $additionalData = array();

    public function __construct($itemName, $errorMessageCode, array $additionalData = array())
    {
        $this->itemName = $itemName;
        $this->errorMessageCode = $errorMessageCode;
        $this->additionalData = $additionalData;
    }

    /**
     * @param array $additionalData
     */
    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param null $errorMessageCode
     */
    public function setErrorMessageCode($errorMessageCode)
    {
        $this->errorMessageCode = $errorMessageCode;
    }

    public function getErrorMessageCode()
    {
        return $this->errorMessageCode;
    }

    /**
     * @param null $itemName
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
    }

    public function getItemName()
    {
        return $this->itemName;
    }
}
