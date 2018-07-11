<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop\db;

use TdbDataExtranetUser;
use TShopBasket;

class AmazonShopPaymentMethod extends \ChameleonSystemAmazonPaymentBundlepkgShopdbAmazonShopPaymentMethodAutoParent
{
    public function IsValidForBasket()
    {
        if (false === parent::IsValidForBasket()) {
            return false;
        }

        if (null !== TShopBasket::GetInstance()->getAmazonOrderReferenceId()) {
            return true === $this->isAmazonPaymentMethod();
        }

        return true;
    }

    public function IsValidForCurrentUser()
    {
        if (false === parent::IsValidForCurrentUser()) {
            return false;
        }

        if (null !== TShopBasket::GetInstance()->getAmazonOrderReferenceId()) {
            return true === $this->isAmazonPaymentMethod() && TdbDataExtranetUser::GetInstance()->isAmazonPaymentUser(
                );
        }

        return true;
    }

    private function isAmazonPaymentMethod()
    {
        return 'amazon' === $this->fieldNameInternal;
    }
}
