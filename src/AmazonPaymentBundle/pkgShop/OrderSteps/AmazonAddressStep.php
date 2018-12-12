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

use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentConfigFactory;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use TdbDataExtranetUser;
use TGlobal;
use TShopBasket;

class AmazonAddressStep extends \TdbShopOrderStep
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

    private function getActivePortalId()
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal()->id;
    }

    protected function &GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $data = parent::GetAdditionalViewVariables($sViewName, $sViewType);
        $data['amazonConfig'] = null;
        $data['oBasket'] = \TShopBasket::GetInstance();
        try {
            $data['amazonConfig'] = AmazonPaymentConfigFactory::createConfig($this->getActivePortalId());
        } catch (\InvalidArgumentException $e) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order_amazon')->warning(
                'error loading amazon payment config'
            );
        }

        return $data;
    }

    protected function ProcessStep()
    {
        $continue = parent::ProcessStep();

        // get selected address details and assign them to the user
        $config = AmazonPaymentConfigFactory::createConfig($this->getActivePortalId());
        $amazonPayment = new AmazonPayment($config);

        try {
            $amazonPayment->updateWithSelectedShippingAddress(
                TShopBasket::GetInstance(),
                \TdbDataExtranetUser::GetInstance()
            );
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $msgManager = \TCMSMessageManager::GetInstance();
            $msgManager->AddMessage('amazonPaymentBasketStep', $e->getMessageCode(), $e->getAdditionalData());
            $continue = false;
        }

        return $continue;
    }

    /**
     * reset address data because customer choose new in this step.
     */
    public function Init()
    {
        parent::Init();
        $oUser = TdbDataExtranetUser::GetInstance();
        $shippingAddress = $oUser->GetShippingAddress();
        if (true === $shippingAddress->getIsAmazonShippingAddress()) {
            $oUser->resetAmazonAddresses();
            $basket = $this->getShopService()->getActiveBasket();
            $basket->RecalculateBasket(); // need to recalculate maybe changing address can change basket amount (gross net change)
        }
    }

    public function GetHtmlFooterIncludes()
    {
        $includes = parent::GetHtmlFooterIncludes();
        $includes[] = "<script type='text/javascript' src='".TGlobal::GetStaticURL('/bundles/chameleonsystemamazonpayment/widgets/address.js')."'></script>";

        return $includes;
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
