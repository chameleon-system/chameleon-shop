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

use TdbDataExtranetUserAddress;

class AmazonPaymentExtranetUser extends \ChameleonSystemAmazonPaymentBundlepkgExtranetUserAmazonPaymentExtranetUserAutoParent
{
    private $amazonPaymentEnabled = false;
    /**
     * @var \TdbDataExtranetUserAddress|null
     */
    private $originalShippingAddress = null;
    /**
     * @var \TdbDataExtranetUserAddress|null
     */
    private $originalBillingAddress = null;

    /**
     * we need this to store our private properties during sleep (php does not support private vars).
     *
     * @var array
     */
    protected $amazonSleepData = null;

    public function __sleep()
    {
        $paramList = parent::__sleep();
        $this->amazonSleepData = array();
        $this->amazonSleepData['amazonPaymentEnabled'] = $this->amazonPaymentEnabled;
        $this->amazonSleepData['originalShippingAddress'] = $this->originalShippingAddress;
        $this->amazonSleepData['originalBillingAddress'] = $this->originalBillingAddress;
        $paramList[] = 'amazonSleepData';

        return $paramList;
    }

    public function __wakeup()
    {
        parent::__wakeup();
        if (is_array($this->amazonSleepData)) {
            $this->amazonPaymentEnabled = $this->amazonSleepData['amazonPaymentEnabled'];
            $this->originalShippingAddress = $this->amazonSleepData['originalShippingAddress'];
            $this->originalBillingAddress = $this->amazonSleepData['originalBillingAddress'];
            $this->amazonSleepData = null;
        }
    }

    public function isAmazonPaymentUser()
    {
        return true === $this->getAmazonPaymentEnabled();
    }

    public function resetAmazonAddresses()
    {
        if (true === $this->oShippingAddress->getIsAmazonShippingAddress()) {
            $oldShippingAddress = $this->GetShippingAddress();
            if (null !== $this->originalShippingAddress) {
                $newShippingAddress = $this->originalShippingAddress;
            } else {
                $newShippingAddress = TdbDataExtranetUserAddress::GetNewInstance();
            }
            $this->hookChangedShippingAddress($oldShippingAddress, $newShippingAddress);
            $this->oShippingAddress = $newShippingAddress;
        }

        if (true === $this->oBillingAddress->getIsAmazonShippingAddress()) {
            $oldBillingAddress = $this->GetShippingAddress();
            if (null !== $this->originalShippingAddress) {
                $newBillingAddress = $this->originalShippingAddress;
            } else {
                $newBillingAddress = TdbDataExtranetUserAddress::GetNewInstance();
            }
            $this->hookChangedBillingAddress($oldBillingAddress, $newBillingAddress);
            $this->oBillingAddress = $newBillingAddress;
        }
    }

    public function setAmazonShippingAddress(\TdbDataExtranetUserAddress $address)
    {
        if (null === $this->oShippingAddress || false === $this->oShippingAddress->getIsAmazonShippingAddress()) {
            $this->originalShippingAddress = $this->oShippingAddress;
        }
        if (null === $this->oBillingAddress || false === $this->oBillingAddress->getIsAmazonShippingAddress()) {
            $this->originalBillingAddress = $this->oBillingAddress;
        }

        $address->setIsAmazonShippingAddress(true);
        $shippingAddress = $this->GetShippingAddress();
        $this->hookChangedShippingAddress($shippingAddress, $address);
        $this->oShippingAddress = $address;

        $billingAddress = $this->GetBillingAddress();
        $this->hookChangedBillingAddress($billingAddress, $address);
        $this->oBillingAddress = $address;
    }

    /**
     * @return bool
     */
    public function getAmazonPaymentEnabled()
    {
        return $this->amazonPaymentEnabled;
    }

    /**
     * @param bool $amazonPaymentEnabled
     */
    public function setAmazonPaymentEnabled($amazonPaymentEnabled)
    {
        $this->amazonPaymentEnabled = $amazonPaymentEnabled;
        if (true === $this->amazonPaymentEnabled) {
            return;
        }

        if (null !== $this->oShippingAddress && false === $this->oShippingAddress->getIsAmazonShippingAddress()) {
            return;
        }

        $shippingAddress = $this->GetShippingAddress();
        if (null === $this->originalShippingAddress) {
            $this->originalShippingAddress = TdbDataExtranetUserAddress::GetNewInstance();
        }
        $this->hookChangedShippingAddress($shippingAddress, $this->originalShippingAddress);
        $this->oShippingAddress = $this->originalShippingAddress;
        $this->originalShippingAddress = null;

        $billingAddress = $this->GetBillingAddress();
        if (null === $this->originalBillingAddress) {
            $this->originalBillingAddress = TdbDataExtranetUserAddress::GetNewInstance();
        }
        $this->hookChangedBillingAddress($billingAddress, $this->originalBillingAddress);
        $this->oBillingAddress = $this->originalBillingAddress;
        $this->originalBillingAddress = null;
    }

    public function UpdateShippingAddress($aAddressData)
    {
        $adr = (null !== $this->oShippingAddress) ? clone $this->oShippingAddress : null;
        if (null !== $adr) {
            $aAddressData['__is_amazon_shipping_address'] = $adr->getIsAmazonShippingAddress();
        }
        $updated = parent::UpdateShippingAddress($aAddressData);
        if (null !== $adr) {
            $this->oShippingAddress->setIsAmazonShippingAddress($adr->getIsAmazonShippingAddress());
        }

        return $updated;
    }

    public function UpdateBillingAddress($aAddressData)
    {
        $adr = (null !== $this->oBillingAddress) ? clone $this->oBillingAddress : null;
        if (null !== $adr) {
            $aAddressData['__is_amazon_shipping_address'] = $adr->getIsAmazonShippingAddress();
        }
        $updated = parent::UpdateBillingAddress($aAddressData);
        if (null !== $adr) {
            $this->oBillingAddress->setIsAmazonShippingAddress($adr->getIsAmazonShippingAddress());
        }

        return $updated;
    }

    public function SetAddressAsShippingAddress($sAddressId)
    {
        if ($this->oShippingAddress->getIsAmazonShippingAddress()) {
            return $this->oShippingAddress;
        }

        return parent::SetAddressAsShippingAddress($sAddressId);
    }

    public function SetAddressAsBillingAddress($sAddressId)
    {
        if ($this->oBillingAddress->getIsAmazonShippingAddress()) {
            return $this->oBillingAddress;
        }

        return parent::SetAddressAsBillingAddress($sAddressId);
    }

    public function GetBillingAddress($bReset = false)
    {
        if (null !== $this->oBillingAddress && $this->oBillingAddress->getIsAmazonShippingAddress()) {
            return $this->oBillingAddress;
        }

        return parent::GetBillingAddress($bReset);
    }

    public function GetShippingAddress($bReset = false, $bGetFromInput = false)
    {
        if (null !== $this->oShippingAddress && $this->oShippingAddress->getIsAmazonShippingAddress()) {
            return $this->oShippingAddress;
        }

        return parent::GetShippingAddress($bReset, $bGetFromInput);
    }
}
