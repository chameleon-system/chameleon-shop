<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps;

use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use TShopBasket;

class AmazonBasketOrderStep extends \ChameleonSystemAmazonPaymentBundlepkgShopOrderStepsAmazonBasketOrderStepAutoParent
{
    public function Init()
    {
        parent::Init();
        $user = $this->getExtranetUserProvider()->getActiveUser();
        $user->setAmazonPaymentEnabled(false);
        TShopBasket::GetInstance()->resetAmazonPaymentReferenceData();
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
