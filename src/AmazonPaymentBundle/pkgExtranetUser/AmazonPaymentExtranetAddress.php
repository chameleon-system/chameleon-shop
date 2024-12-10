<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgExtranetUser;

class AmazonPaymentExtranetAddress extends \ChameleonSystemAmazonPaymentBundlepkgExtranetUserAmazonPaymentExtranetAddressAutoParent
{
    private $isAmazonShippingAddress = false;

    /**
     * @return bool
     */
    public function getIsAmazonShippingAddress()
    {
        return $this->isAmazonShippingAddress;
    }

    /**
     * @param bool $isAmazonShippingAddress
     */
    public function setIsAmazonShippingAddress($isAmazonShippingAddress)
    {
        $this->isAmazonShippingAddress = $isAmazonShippingAddress;
    }

    /**
     * @return string|false
     */
    public function Save()
    {
        if (true === $this->getIsAmazonShippingAddress()) {
            return false;
        }

        return parent::Save();
    }

    public function Delete()
    {
        if (true === $this->getIsAmazonShippingAddress()) {
            return;
        }
        parent::Delete();
    }

    /**
     * @return false|string
     */
    public function SaveFieldsFast($aFields)
    {
        if (true === $this->getIsAmazonShippingAddress()) {
            if ($this->AllowEdit()) {
                foreach ($aFields as $sFieldName => $sVal) {
                    $this->sqlData[$sFieldName] = $sVal;
                }
                // load data so the post load hook is called
                $this->LoadFromRow($this->sqlData);
            }

            return false;
        }

        return parent::SaveFieldsFast($aFields);
    }

    protected function PostLoadHook()
    {
        parent::PostLoadHook();
        if (isset($this->sqlData['__is_amazon_shipping_address'])) {
            $isAmazonShippingAddress = (true === $this->sqlData['__is_amazon_shipping_address']);
            $this->setIsAmazonShippingAddress($isAmazonShippingAddress);
            unset($this->sqlData['__is_amazon_shipping_address']);
        }
    }
}
