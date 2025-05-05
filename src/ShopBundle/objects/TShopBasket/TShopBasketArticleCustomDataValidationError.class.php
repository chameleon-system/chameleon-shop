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
    /**
     * @var string
     */
    private $itemName;

    /**
     * @var string
     */
    private $errorMessageCode;

    /**
     * @var array
     */
    private $additionalData = [];

    /**
     * @param string $itemName
     * @param string $errorMessageCode
     */
    public function __construct($itemName, $errorMessageCode, array $additionalData = [])
    {
        $this->itemName = $itemName;
        $this->errorMessageCode = $errorMessageCode;
        $this->additionalData = $additionalData;
    }

    /**
     * @param array $additionalData
     *
     * @return void
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
     * @param string $errorMessageCode
     *
     * @return void
     */
    public function setErrorMessageCode($errorMessageCode)
    {
        $this->errorMessageCode = $errorMessageCode;
    }

    /**
     * @return string
     */
    public function getErrorMessageCode()
    {
        return $this->errorMessageCode;
    }

    /**
     * @param string $itemName
     *
     * @return void
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
    }

    /**
     * @return string
     */
    public function getItemName()
    {
        return $this->itemName;
    }
}
