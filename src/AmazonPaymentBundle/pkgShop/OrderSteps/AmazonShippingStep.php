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
use TGlobal;
use TShopBasket;

class AmazonShippingStep extends \TShopStepShipping
{
    /**
     * {@inheritdoc}
     */
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        $user = $this->getExtranetUserProvider()->getActiveUser();

        return true === $user->isAmazonPaymentUser() &&
            false === $this->getShopService()->getActiveBasket()->hasAmazonPaymentError();
    }

    protected function isApplicableForBasket(TShopBasket $basket)
    {
        return null !== $basket->getAmazonOrderReferenceId() || true === $basket->hasAmazonPaymentError();
    }

    public function GetHtmlFooterIncludes()
    {
        $includes = parent::GetHtmlFooterIncludes();

        $includes[] = "<script type='text/javascript' src='".TGlobal::GetStaticURL('/bundles/chameleonsystemamazonpayment/widgets/wallet.js')."'></script>";

        return $includes;
    }

    public function ChangeShippingGroup()
    {
        parent::ChangeShippingGroup();
        TShopBasket::GetInstance()->RecalculateBasket(); // need to recalculate so that the new value is sent to amazon
    }

    /**
     * {@inheritdoc}
     */
    public function Init()
    {
        parent::Init();
        if (false === $this->AllowAccessToStep(true)) {
            $this->JumpToStep($this->GetPreviousStep());
        }
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
